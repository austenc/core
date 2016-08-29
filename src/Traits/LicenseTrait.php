<?php namespace Hdmaster\Core\Traits;

trait LicenseTrait
{

    /**
     * Generates a new unique testmaster license for a facility or person
     * Guaranteed to be unused/unique between both facility_person and facility_discipline tables
     * Checks tables facility_discipline and facility_person
     * 
     * (i.e. a facility license will never be used by any person in the state, active or deactive)
     *
     * $base   - first digits of new license
     * $length - length of generated unique license
     */
    public function generateTestmasterLicense($base='', $length=6)
    {
        $faker      = \Faker\Factory::create();
        $newLicense = '';
        $att        = 0;
        $max        = 100;    // prevent possible infinite loop
        $suffixLen  = $length - strlen($base);

        // base must be less than length
        if (strlen($base) >= $length) {
            return false;
        }

        $suffixLength = $length - strlen($base);

        while (empty($newLicense) && ($att < $max)) {
            // if theres a base try to go sequentially
            if (! empty($base)) {
                $tmpLicense = $base . str_pad($att, $suffixLen, '0', STR_PAD_LEFT);
            }
            // no base, generate any random unique
            else {
                $tmpLicense = (string) $faker->randomNumber($length);
            }

            // ensure license is requested length
            // pad zeroes to the right if not
            if (strlen($tmpLicense) != $length) {
                $tmpLicense = str_pad($tmpLicense, $length, '0', STR_PAD_RIGHT);
            }

            // test uniqueness
            if ($this->checkTestmasterLicense($tmpLicense)) {
                $newLicense = $tmpLicense;
            }

            $att++;
        }

        return $newLicense;
    }

    /**
     * Checks if a license is unique against all facility and person licenses
     *
     * TRUE - unique!
     */
    public function checkTestmasterLicense($license)
    {
        $facRes    = \DB::table('facility_discipline')->where('tm_license', $license)->first();
        $personRes = \DB::table('facility_person')->where('tm_license', $license)->first();

        return is_null($facRes) && is_null($personRes);
    }
}
