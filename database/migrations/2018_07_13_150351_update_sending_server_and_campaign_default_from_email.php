<?php

use Illuminate\Database\Migrations\Migration;

class UpdateSendingServerAndCampaignDefaultFromEmail extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $servers = \horsefly\Model\SendingServer::whereRaw("default_from_email = '' or default_from_email is null")->get();
        foreach ($servers as $server) {
            $server->default_from_email = 'default@localhost.localdomain';
            $server->save();
        }

        $campaigns = \horsefly\Model\Campaign::whereRaw('use_default_sending_server_from_email is null')->get();
        foreach ($campaigns as $campaign) {
            $campaign->use_default_sending_server_from_email = false;
            $campaign->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        //
    }
}
