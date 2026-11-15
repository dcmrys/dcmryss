<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_transactions', function (Blueprint $table) {
            $table->string('reference_no', 40)->nullable()->unique()->after('id');
            $table->foreignId('membership_application_id')
                ->nullable()
                ->unique()
                ->after('member_id')
                ->constrained('membership_applications')
                ->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_no', 40)->nullable()->unique()->after('id');
        });

        foreach (DB::table('membership_transactions')->whereNull('reference_no')->orderBy('id')->get(['id']) as $transaction) {
            DB::table('membership_transactions')->where('id', $transaction->id)->update([
                'reference_no' => 'TXN-LEGACY-'.$transaction->id,
            ]);
        }

        foreach (DB::table('payments')->whereNull('payment_no')->orderBy('id')->get(['id']) as $payment) {
            DB::table('payments')->where('id', $payment->id)->update([
                'payment_no' => 'PAY-LEGACY-'.$payment->id,
            ]);
        }

        $applications = DB::table('membership_applications')->where('status', 'approved')->orderBy('id')->get();
        foreach ($applications as $application) {
            if (DB::table('membership_transactions')->where('membership_application_id', $application->id)->exists()) {
                continue;
            }

            $member = DB::table('members')->where('email', $application->email)->first();
            if (! $member) {
                continue;
            }

            $level = DB::table('membership_levels')->where('id', $member->membership_level_id)->first();
            if (! $level) {
                continue;
            }

            $expired = $member->status === 'active'
                && $member->expires_at
                && $member->expires_at < now()->toDateString();

            DB::table('membership_transactions')->insert([
                'reference_no' => 'TXN-LEGACY-APP-'.$application->id,
                'membership_application_id' => $application->id,
                'member_id' => $member->id,
                'membership_category_id' => $member->membership_category_id,
                'membership_level_id' => $member->membership_level_id,
                'type' => 'application',
                'status' => $expired ? 'expired' : 'approved',
                'amount_due' => $level->fee,
                'starts_on' => $member->joined_at,
                'ends_on' => $member->expires_at,
                'processed_at' => $application->updated_at,
                'notes' => 'Historical transaction for approved application '.$application->reference_no,
                'created_at' => $application->created_at,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('membership_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('membership_application_id');
            $table->dropUnique(['reference_no']);
            $table->dropColumn('reference_no');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['payment_no']);
            $table->dropColumn('payment_no');
        });
    }
};
