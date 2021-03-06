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
            return DateTimeZone::listIdentifiers( DateTimeZone::AMERICA );;
        }


        /**
         * Get Relative time
         * @param string $text a date/time string
         * @param string $timezone
         * @param string $format
         * @return string
         * @throws Exception
         */
        public function GetRelativeTime( string $text, $timezone = 'UTC', $format = 'c' ): string {
            $result = new DateTime( $text, new DateTimeZone( $timezone ) );
            return $result->format( $format );
        }


        /**
         * Implode Function
         * @param string   $glue
         * @param string[] $pieces
         * @return string string
         */
        public function Implode( $glue, $pieces = [ '1', '2', '3' ] ) {
            return implode( $glue, $pieces );
        }
    }