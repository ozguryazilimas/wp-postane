<?php


class WAPT_FoundedImage implements JsonSerializable {

    /**
     * @var string
     */
    public $link;

    /**
     * @var string
     */
    public $context_link;

    /**
     * @var string
     */
    public $thumbnail_link;

    /**
     * @var string
     */
    public $title;

    /**
     * @var int
     */
    public $width;

    /**
     * @var int
     */
    public $height;

    /**
     * @var array
     */
    public $more_info;


    /**
     * WAPT_FoundedImage constructor.
     *
     * @param string $link
     * @param string $context_link
     * @param string $thumbnail_link
     * @param string $title
     * @param int $width
     * @param int $height
     * @param array $more_info
     */
    public function __construct( $link, $context_link, $thumbnail_link, $title, $width, $height, $more_info = []) {
        $this->link = $link;
        $this->context_link = $context_link;
        $this->thumbnail_link = $thumbnail_link;
        $this->title = $title;
        $this->width = $width;
        $this->height = $height;
        $this->more_info = $more_info;
    }

    public function jsonSerialize() {
        return [
            'link' => $this->link,
            'context_link' => $this->context_link,
            'thumbnail_link' => $this->thumbnail_link,
            'title' => $this->title,
            'width' => $this->width,
            'height' => $this->height,
            'more_info' => $this->more_info,
        ];
    }
}
