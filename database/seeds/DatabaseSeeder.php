<?php

namespace Seeds;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    private $seeders;

    /**
     * DatabaseSeeder constructor.
     */
    public function __construct()
    {
        /*
         * Configure each environment separately because order matters
         */
        if (\App::environment() == 'production') {
            $this->seeders = [
                Local\AdminsTableSeeder::class,
            ];
        } else if (\App::environment() == 'staging') {
            $this->seeders = [
                Local\AdminsTableSeeder::class,
                Staging\TeachersTableSeeder::class,

                Staging\LevelsTableSeeder::class,
                Staging\LevelTeacherTableSeeder::class,

                Staging\LabelsTableSeeder::class,
                Staging\LabelTeacherTableSeeder::class,

                Staging\TimeslotsTableSeeder::class,
                Staging\TeacherTimeSlotTableSeeder::class,
            ];
        } else {
            $this->seeders = [
                Local\StudentsTableSeeder::class,
                Local\TeachersTableSeeder::class,
                Local\AdminsTableSeeder::class,

                Local\LevelsTableSeeder::class,
                Local\LevelTeacherPivotTableSeeder::class,

                Local\LabelsTableSeeder::class,
                Local\LabelTeacherPivotTableSeeder::class,

                Local\TimeslotsTableSeeder::class,
                Local\TeacherTimeSlotPivotTableSeeder::class,

                // Local\TutorialsTableSeeder::class,
                // Local\LecturesTableSeeder::class,
                // Local\OffTimesTableSeeder::class,
            ];
        }
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->seeders as $seeder) {
            $this->call($seeder);
        }
    }
}
