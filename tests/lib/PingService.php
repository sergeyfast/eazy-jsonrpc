<?php


    /**
     * Simple Echo Service
     */
    class PingService {

        /**
         * Get Ping Result
         * @param string $message
         * @return string pong
         */
        public function Ping( $message = 'pong' ) {
            return $message;
        }

    }