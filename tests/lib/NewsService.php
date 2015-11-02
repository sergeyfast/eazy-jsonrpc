<?php


    /**
     * Simple News Service
     */
    class NewsService {

        /**
         * Get News List
         * @return News[]
         */
        public function GetList() {
            $t1 = new Tag( 1, 't1' );
            $t2 = new Tag( 2, 't2' );
            $t3 = new Tag( 3, 't3' );
            $c1 = new Category( 1, 'c1', [ $t1, $t3 ], [ 1, 3 ] );
            $c2 = new Category( 2, 'c2', [ $t1, $t2 ], [ 1, 2 ] );
            $n1 = new News( 1, 'n1', $c1, false, 3.5, [ $t1 ] );
            $n2 = new News( 2, 'n2', $c2, false, 3.5, [ ] );
            $n3 = new News( 3, 'n3', $c1, true, 5.5, [ $t1, $t2, $t3 ] );

            return [ 1 => $n1, 2 => $n2, 3 => $n3 ];
        }


        /**
         * Get News By Id
         * @param int $id
         * @return News
         */
        public function GetById( $id ) {
            $t1 = new Tag( 1, 't1' );
            $t3 = new Tag( 3, 't3' );
            $c1 = new Category( 1, 'c1', [ $t1, $t3 ], [ 1, 3 ] );
            $n1 = new News( 1, 'n1', $c1, false, 3.5, [ $t1 ] );

            return $n1;
        }


        /**
         * Get Tags
         * @return Tag[]
         */
        public function GetTags() {
            return [ new Tag( 1, 't1' ), new Tag( 2, 't2' ), new Tag( 3, 't3' ) ];
        }


        /**
         * Get Categories
         * @return string[]
         */
        public function Categories() {
            return [ 1 => 'test', 2 => 'test2' ];
        }


        /**
         * Search News
         * @param NewsSearch $s
         * @param int        $page
         * @param int        $count
         * @return News[]
         */
        public function Search( $s, $page, $count = 50 ) {
            return array_slice( self::GetList(), $page, $count );
        }


        /**
         * Name Value
         * @param NameValue[] $nv
         * @param int         $c
         * @return string[]
         */
        public function NameValue( $nv, $c = null ) {
            return array_map( function ( $x ) {
                return $x->name;
            }, $nv );
        }
    }