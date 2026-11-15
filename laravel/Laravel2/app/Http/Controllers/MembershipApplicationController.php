<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMembershipApplicationRequest;
use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\User;
use App\Notifications\MembershipApproved;
use App\Notifications\MembershipApplicationSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MembershipApplicationController extends Controller
{
    public function index()
    {
        $pendingCount = MembershipApplication::where('status', 'pending')->count();
        $applications = MembershipApplication::with('category')
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(15);
        return view('membership.index', compact('applications', 'pendingCount'));
    }

    public function updateStatus(Request $request, MembershipApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $accountCreated = false;
        $wasApproved = $application->status === 'approved';
        $transactionReference = null;
        $applicationTransaction = null;

        DB::transaction(function () use ($application, $validated, $wasApproved, &$accountCreated, &$transactionReference, &$applicationTransaction) {
            $application->update(['status' => $validated['status']]);

            if ($validated['status'] !== 'approved') {
                return;
            }

            $level = MembershipLevel::orderBy('fee')->orderBy('id')->first();
            if (! $level) {
                throw ValidationException::withMessages([
                    'status' => 'Add a membership level before approving an application.',
                ]);
            }
            $nameParts = preg_split('/\s+/', trim($application->full_name), 2) ?: [];
            $member = Member::firstOrCreate(['email' => $application->email], [
                'user_id' => User::where('email', $application->email)->where('role', 'member')->value('id'),
                'first_name' => $nameParts[0] ?? trim($application->full_name),
                'last_name' => $nameParts[1] ?? '',
                'phone' => $application->phone,
                'address' => $application->address,
                'membership_category_id' => $application->membership_category_id,
                'membership_level_id' => $level->id,
                'status' => 'pending',
                'joined_at' => null,
                'expires_at' => null,
                'notes' => 'Approved application: '.$application->reference_no,
            ]);

            if (! $member->member_number) {
                $member->update(['member_number' => sprintf('MB-%06d', $member->id)]);
            }

            $user = $member->user ?? User::where('email', $application->email)->first();
            if ($user && $user->role !== 'member') {
                throw ValidationException::withMessages([
                    'email' => 'This application email already belongs to a non-member account.',
                ]);
            }

            if (! $user) {
                $user = User::create([
                    'name' => $member->full_name,
                    'email' => $member->email,
                    'password' => Str::random(64),
                    'role' => 'member',
                    'password_setup_required' => true,
                ]);
                $accountCreated = true;
            }

            if ($member->user_id !== $user->id) {
                $member->update(['user_id' => $user->id]);
            }

            if (! $wasApproved) {
                $user->forceFill(['password_setup_required' => true])->save();
            }

            $transactionStatus = $wasApproved && $member->status === 'active'
                ? 'approved'
                : ((float) $level->fee <= 0 ? 'paid' : 'pending');

            $transaction = MembershipTransaction::firstOrCreate(
                ['membership_application_id' => $application->id],
                [
                    'reference_no' => 'TXN-'.Str::upper((string) Str::ulid()),
                    'member_id' => $member->id,
                    'membership_category_id' => $application->membership_category_id,
                    'membership_level_id' => $level->id,
                    'type' => 'application',
                    'status' => $transactionStatus,
                    'amount_due' => $level->fee,
                    'starts_on' => $wasApproved ? $member->joined_at : null,
                    'ends_on' => $wasApproved ? $member->expires_at : null,
                    'processed_at' => $wasApproved ? ($application->updated_at ?? now()) : null,
                    'notes' => 'Membership application '.$application->reference_no,
                ]
            );
            $transactionReference = $transaction->reference_no;
            $applicationTransaction = $transaction;
        });

        if ($validated['status'] === 'approved') {
            Notification::route('mail', $application->email)
                ->notify(new MembershipApproved($application, $applicationTransaction));
        }

        $message = "Application status updated to {$validated['status']}.";
        if ($validated['status'] === 'approved') {
            $message = "Application approved. Transaction {$transactionReference} status: ".ucfirst($applicationTransaction->status).'.';
            if ($accountCreated) {
                $message .= ' Member profile and login created.';
            }
        }

        return redirect()->route('applications.index')->with('success', $message);
    }

    public function create()
    {
        return view('membership.create', [
            'categories' => MembershipCategory::orderBy('name')->get(),
        ]);
    }

    public function store(StoreMembershipApplicationRequest $request)
    {
        $application = DB::transaction(function () use ($request) {
            $application = MembershipApplication::create([
                ...$request->validated(),
                'reference_no' => 'MEM-'.Str::upper((string) Str::ulid()),
                'notification_queued_at' => now(),
            ]);

            Notification::route('mail', $application->email)
                ->notify(new MembershipApplicationSubmitted($application));

            return $application;
        });

        return redirect()->route('membership.create')->with(
            'success', "Application saved. Your reference number is {$application->reference_no}. A confirmation email has been queued."
        );
    }
}
