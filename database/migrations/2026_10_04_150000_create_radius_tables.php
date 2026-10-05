<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. radcheck
        DB::statement("
            CREATE TABLE IF NOT EXISTS radcheck (
                id serial PRIMARY KEY,
                username text NOT NULL DEFAULT '',
                attribute text NOT NULL DEFAULT '',
                op varchar(2) NOT NULL DEFAULT '==',
                value text NOT NULL DEFAULT ''
            );
        ");
        DB::statement("CREATE INDEX IF NOT EXISTS radcheck_username ON radcheck (username, attribute);");

        // 2. radreply
        DB::statement("
            CREATE TABLE IF NOT EXISTS radreply (
                id serial PRIMARY KEY,
                username text NOT NULL DEFAULT '',
                attribute text NOT NULL DEFAULT '',
                op varchar(2) NOT NULL DEFAULT '=',
                value text NOT NULL DEFAULT ''
            );
        ");
        DB::statement("CREATE INDEX IF NOT EXISTS radreply_username ON radreply (username, attribute);");

        // 3. radgroupcheck
        DB::statement("
            CREATE TABLE IF NOT EXISTS radgroupcheck (
                id serial PRIMARY KEY,
                groupname text NOT NULL DEFAULT '',
                attribute text NOT NULL DEFAULT '',
                op varchar(2) NOT NULL DEFAULT '==',
                value text NOT NULL DEFAULT ''
            );
        ");
        DB::statement("CREATE INDEX IF NOT EXISTS radgroupcheck_groupname ON radgroupcheck (groupname, attribute);");

        // 4. radgroupreply
        DB::statement("
            CREATE TABLE IF NOT EXISTS radgroupreply (
                id serial PRIMARY KEY,
                groupname text NOT NULL DEFAULT '',
                attribute text NOT NULL DEFAULT '',
                op varchar(2) NOT NULL DEFAULT '=',
                value text NOT NULL DEFAULT ''
            );
        ");
        DB::statement("CREATE INDEX IF NOT EXISTS radgroupreply_groupname ON radgroupreply (groupname, attribute);");

        // 5. radusergroup
        DB::statement("
            CREATE TABLE IF NOT EXISTS radusergroup (
                id serial PRIMARY KEY,
                username text NOT NULL DEFAULT '',
                groupname text NOT NULL DEFAULT '',
                priority integer NOT NULL DEFAULT 0
            );
        ");
        DB::statement("CREATE INDEX IF NOT EXISTS radusergroup_username ON radusergroup (username);");

        // 6. radacct
        DB::statement("
            CREATE TABLE IF NOT EXISTS radacct (
                radacctid bigserial PRIMARY KEY,
                acctsessionid text NOT NULL,
                acctuniqueid text NOT NULL UNIQUE,
                username text,
                realm text,
                nasipaddress inet NOT NULL,
                nasportid text,
                nasporttype text,
                acctstarttime timestamp with time zone,
                acctupdatetime timestamp with time zone,
                acctstoptime timestamp with time zone,
                acctinterval bigint,
                acctsessiontime bigint,
                acctauthentic text,
                connectinfo_start text,
                connectinfo_stop text,
                acctinputoctets bigint,
                acctoutputoctets bigint,
                calledstationid text,
                callingstationid text,
                acctterminatecause text,
                servicetype text,
                framedprotocol text,
                framedipaddress inet,
                framedipv6address inet,
                framedipv6prefix inet,
                framedinterfaceid text,
                delegatedipv6prefix inet,
                class text
            );
        ");
        DB::statement("CREATE INDEX IF NOT EXISTS radacct_active_session_idx ON radacct (acctuniqueid) WHERE acctstoptime IS NULL;");
        DB::statement("CREATE INDEX IF NOT EXISTS radacct_bulk_close ON radacct (nasipaddress, acctstarttime) WHERE acctstoptime IS NULL;");
        DB::statement("CREATE INDEX IF NOT EXISTS radacct_start_user_idx ON radacct (acctstarttime, username);");

        // 7. radpostauth
        DB::statement("
            CREATE TABLE IF NOT EXISTS radpostauth (
                id bigserial PRIMARY KEY,
                username text NOT NULL,
                pass text,
                reply text,
                calledstationid text,
                callingstationid text,
                authdate timestamp with time zone NOT NULL DEFAULT now(),
                class text
            );
        ");
        DB::statement("CREATE INDEX IF NOT EXISTS radpostauth_username_idx ON radpostauth (username);");
        DB::statement("CREATE INDEX IF NOT EXISTS radpostauth_class_idx ON radpostauth (class);");

        // 8. nas
        DB::statement("
            CREATE TABLE IF NOT EXISTS nas (
                id serial PRIMARY KEY,
                nasname text NOT NULL,
                shortname text NOT NULL,
                type text NOT NULL DEFAULT 'other',
                ports integer,
                secret text NOT NULL,
                server text,
                community text,
                description text
            );
        ");
        DB::statement("CREATE INDEX IF NOT EXISTS nas_nasname ON nas (nasname);");

        // 9. nasreload
        DB::statement("
            CREATE TABLE IF NOT EXISTS nasreload (
                nasipaddress inet PRIMARY KEY,
                reloadtime timestamp with time zone NOT NULL
            );
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('nasreload');
        Schema::dropIfExists('nas');
        Schema::dropIfExists('radpostauth');
        Schema::dropIfExists('radacct');
        Schema::dropIfExists('radusergroup');
        Schema::dropIfExists('radgroupreply');
        Schema::dropIfExists('radgroupcheck');
        Schema::dropIfExists('radreply');
        Schema::dropIfExists('radcheck');
    }
};
