<?php

use App\Models\Eventpage;
use App\Models\MeetingRoom;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('meeting_rooms') && ! Schema::hasColumn('meeting_rooms', 'min_persons')) {
            Schema::table('meeting_rooms', function (Blueprint $table) {
                $table->unsignedInteger('min_persons')->default(0)->after('slug');
            });
        }

        if (! Schema::hasTable('meeting_rooms') || ! Schema::hasTable('eventpages')) {
            return;
        }

        Eventpage::query()->orderBy('id')->each(function (Eventpage $page) {
            MeetingRoom::syncCanonicalForEventpage($page);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('meeting_rooms') && Schema::hasColumn('meeting_rooms', 'min_persons')) {
            Schema::table('meeting_rooms', function (Blueprint $table) {
                $table->dropColumn('min_persons');
            });
        }
    }
};
