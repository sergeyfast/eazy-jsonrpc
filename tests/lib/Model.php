<?php


    /**
     * News
     */
    class News {

        /**
         * @var int
         */
        public $newsId;

        /**
         * @var string
         */
        public $title;

        /**
         * @var Category
         */
        public $category;

        /**
         * @var bool
         */
        public $isPublished;

        /**
         * @var float
         */
        public $totalRating;

        /**
         * @var Tag[]
         */
        public $tags;


        /**
         * News constructor.
         * @param int      $newsId
         * @param string   $title
         * @param Category $category
         * @param bool     $isPublished
         * @param float    $totalRating
         * @param Tag[]    $tags
         */
        public function __construct( $newsId, $title, Category $category, $isPublished, $totalRating, array $tags ) {
            $this->newsId      = $newsId;
            $this->title       = $title;
            $this->category    = $category;
            $this->isPublished = $isPublished;
            $this->totalRating = $totalRating;
            $this->tags        = $tags;
        }
    }


    /**
     * News Category
     */
    class Category {

        /**
         * @var int
         */
        public $categoryId;

        /**
         * @var string
         */
        public $title;

        /**
         * @var Tag[]
         */
        public $tags;

        /**
         * @var int[]
         */
        public $tagIds;


        /**
         * Category constructor.
         * @param int    $categoryId
         * @param string $title
         * @param Tag[]  $tags
         * @param int[]  $tagIds
         */
        public function __construct( $categoryId, $title, array $tags, array $tagIds ) {
            $this->categoryId = $categoryId;
            $this->title      = $title;
            $this->tags       = $tags;
            $this->tagIds     = $tagIds;
        }
    }


    /**
     * Category Tag
     */
    class Tag {

        /**
         * @var int
         */
        public $tagId;

        /**
         * @var string
         */
        public $title;


        /**
         * Tag constructor.
         * @param int    $tagId
         * @param string $title
         */
        public function __construct( $tagId, $title ) {
            $this->tagId = $tagId;
            $this->title = $title;
        }
    }


    /**
     * NewsSearch
     */
    class NewsSearch {

        /**
         * @var int
         */
        public $id;


        /**
         * @var int[]
         */
        public $tagIds;


        /**
         * @var bool
         */
        public $isPublished;

        /**
         * @var Tag[]
         */
        public $tags;

        /**
         * @var Category
         */
        public $category;
    }


    /**
     * NameValue Parameter
     */
    class NameValue {

        /**
         * @var string
         */
        public $name;

        /**
         * @var string
         */
        public $value;

    }