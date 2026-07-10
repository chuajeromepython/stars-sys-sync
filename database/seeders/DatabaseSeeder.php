<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call([
            AcademicYearSeeder::class,
            AssessmentTypeSeeder::class,
            ClassificationSeeder::class,
            // CompetencySeeder::class,  /*  */
            CourseSeeder::class,
            DistrictSeeder::class,
            DivisionAdministratorSeeder::class,
            DivisionSeeder::class,
            // ECDCDomainSeeder::class, /*  */
            // ECDCCompetencySeeder::class, /*  */
            GradeLevelSeeder::class,
            PeriodSeeder::class,
            PersonSeeder::class,
            SemesterSeeder::class,
            // SectionSeeder::class,
            SchoolSeeder::class,
            SchoolTypeSeeder::class,
            SchoolCategorySeeder::class,
            StrandSeeder::class,
            SubjectSeeder::class,
            SubjectComponentSeeder::class,
            TrackSeeder::class,
            UserSeeder::class,
            WeekSeeder::class,
            // SchoolSupervisorSeeder::class,
        ]);
    }
}
