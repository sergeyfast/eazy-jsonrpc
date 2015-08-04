<?php


    /**
     * Simple Date Time Service
     */
    class DateTimeService {

        /**
         * Get Current Time
         * @param string $timezone
         * @param string $format
         * @return string
         */
        public function GetTime( $timezone = 'UTC', $format = 'c' ) {
            $result = new DateTime( 'now', new DateTimeZone( $timezone ) );
            return $result->format( $format );
        }


        /**
         * Returns associative array containing dst, offset and the timezone name
         * @return array
         */
        public function GetTimeZones() {
            return array_slice( DateTimeZone::listAbbreviations(), 0, 3 );
        }


        /**
         * Get Relative time
         * @param string $text a date/time string
         * @param string $timezone
         * @param string $format
         * @return string
         */
        public function GetRelativeTime( $text, $timezone = 'UTC', $format = 'c' ) {
            $result = new DateTime( $text, new DateTimeZone( $timezone ) );
            return $result->format( $format );
        }


        /**
         * Implode Function
         * @param string   $glue
         * @param string[] $pieces
         * @return string string
         */
        public function Implode( $glue, $pieces = array( "1", "2", "3" ) ) {
            return implode( $glue, $pieces );
        }


        /**
         * ComplexResult Function
         * @result({
         *      "id":{"type":"string" },
         *      "firstName":{"type":"string"},
         *      "lastName":{"type":"string"},
         *      "age":{"type":"number","maximum":125,"minimum":0},
         *      "address":{"type":"string"}
         * })
         *
         * @return StdClass
         */
        public function ComplexResult() {
            $result            = new StdClass();
            $result->id        = 12;
            $result->firstName = "John";
            $result->lastName  = "Smith";
            $result->age       = 24;
            $result->address   = "Spb";

            return $result;
        }
    }