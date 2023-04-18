<?php
namespace CommonbookingsAdditionalFeatures;

class Booking
{
    public $bookingID;
    public $itemID;
    public $userId;
    public $fullDay;
    public $endTime;
    public $startTime;
    public $repetitionEnd;
    public $dateEnd;
    public $repetitionStart;
    public $dateStart;
    public $itemName;
    public $locationName;
    public $locationID;
    public $locationStreet;
    public $locationPostcode;
    public $locationCity;
    public $locationCountry;
    public $postTitle;
    public $postStatus;
    public $buchungsDauer;
    public $picture;
    public $link;

    public function __construct(int $id, int $userId)
    {
        $this->bookingID = $id;
        $this->itemID = get_post_meta($id, 'item-id', true);
        $this->locationID = get_post_meta($id, 'location-id', true);
        $this->userId = $userId;
        $this->picture = get_the_post_thumbnail_url(
            get_post_meta($id, 'item-id', true),
            'post-thumbnail'
        );
        $this->fullDay =
            get_post_meta($id, 'full-day', true) == 'on' ? true : false;
        $this->endTime = get_post_meta($id, 'end-time', true);
        $this->startTime = get_post_meta($id, 'start-time', true);
        $this->repetitionEnd = get_post_meta($id, 'repetition-end', true);
        $this->dateEnd = gmdate(
            'd.m.Y',
            intval(get_post_meta($id, 'repetition-end', true))
        );
        $this->repetitionStart = get_post_meta($id, 'repetition-start', true);
        $this->dateStart = gmdate(
            'd.m.Y',
            intval(get_post_meta($id, 'repetition-start', true))
        );
        $this->locationName = get_post(
            get_post_meta($id, 'location-id', true)
        )->post_title;
        $this->itemName = get_post(
            get_post_meta($id, 'item-id', true)
        )->post_title;
        $this->locationStreet = get_post_meta(
            get_post_meta($id, 'location-id', true),
            '_cb_location_street',
            true
        );
        $this->locationPostcode = get_post_meta(
            get_post_meta($id, 'location-id', true),
            '_cb_location_postcode',
            true
        );
        $this->locationCity = get_post_meta(
            get_post_meta($id, 'location-id', true),
            '_cb_location_city',
            true
        );
        $this->locationCountry = get_post_meta(
            get_post_meta($id, 'location-id', true),
            '_cb_location_country',
            true
        );
        $this->postTitle = get_post($id)->post_title;
        $this->postStatus = get_post($id)->post_status;
        $this->link = WP_SITEURL . '/?cb_booking=' . get_post($id)->post_name;
        $this->buchungsDauer = Date::diff(
            $this->dateStart . ' ' . $this->startTime,
            $this->dateEnd . ' ' . $this->endTime
        );
        if($this->postStatus == "confirmed" && $this->repetitionEnd < time()){
            $this->postStatus = "past";
        }
    }

    public static function comparatorUp($object1, $object2)
    {
        return $object1->repetitionStart > $object2->repetitionStart;
    }
    public static function comparatorDown($object1, $object2)
    {
        return $object1->repetitionStart < $object2->repetitionStart;
    }
}
