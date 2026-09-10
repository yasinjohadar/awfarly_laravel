<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemoveProposalsFeatureData extends Migration
{
    /**
     * Run the migrations.
     *
     * Permanently removes the "Proposals" feature's data: the proposals table
     * itself, reports/media attached to proposals, and the proposals and
     * statistics.proposals permission and settings rows (both the Spatie
     * permissions tables and this app's permissions_groups/permissions_groups_data
     * catalog tables used to render the role-permissions admin UI).
     *
     * @return void
     */
    public function up()
    {
        // Reports filed against proposals
        DB::table('reports')->where('reported_type', 'App\\Models\\Proposals\\Proposal')->delete();

        // Media attached to proposals (Spatie MediaLibrary)
        DB::table('media')->where('model_type', 'App\\Models\\Proposals\\Proposal')->delete();

        // Spatie permissions + their role/model assignments
        $permissionIds = DB::table('permissions')
            ->where('name', 'like', 'proposals.%')
            ->orWhere('name', 'statistics.proposals')
            ->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();

        // Permission catalog tables driving the admin "manage roles" UI
        DB::table('permissions_groups_data')
            ->where('key', 'like', 'proposals.%')
            ->orWhere('key', 'statistics.proposals')
            ->delete();
        DB::table('permissions_groups')->where('name', 'Proposals')->delete();

        // App settings
        DB::table('settings')->where('type', 'proposals')->delete();

        // The table itself
        Schema::dropIfExists('proposals');
    }

    /**
     * Reverse the migrations.
     *
     * Deliberately irreversible — restore from the pre-deletion backup
     * (scratchpad/full_db_backup_*.sql) if the Proposals feature is ever
     * needed again.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
