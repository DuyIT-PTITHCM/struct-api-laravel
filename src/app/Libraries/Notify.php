<?php

namespace App\Libraries;

use App\Models\Model;
use App\Models\Notification;

/**
 * Class Notify
 * @package App\Libraries
 * @method static campaign($typeId, $userId, $key, $value = '')
 * @method static inquiry($typeId, $userId, $key, $value = '')
 * @method static notice($typeId, $userId = 0, $key, $value = '', $device_os = '')
 */
class Notify
{
    private static $types = [
        'campaign',
        'inquiry',
        'notice'
    ];

    /**
     * @param $name
     * @param array $arguments
     */
    public static function __callStatic($name, array $arguments)
    {
        in_array($name, self::$types) && self::create($name, ...$arguments);
    }

    /**
     * @param $type
     * @param $typeId
     * @param $userId
     * @param $key
     * @param string $value
     */
    public static function create($type, $typeId, $userId, $key, $value = '', $deviceOs = [])
    {
        $notification = new Notification();
        $notification->hash = Helper::generateHash();
        $notification->type = $type;
        $notification->type_id = $typeId;
        $notification->user_id = $userId;
        $notification->event_key = $key;
        $notification->event_value = $value;
        if (!empty($deviceOs)) {
            $notification->device_os = $deviceOs[0];
        }
        $notification->save();
    }

    /**
     * @param $noticeId
     */
    public static function deleteNotice($noticeId)
    {
        $result = Notification::where('type', 'notice')
            ->where('type_id', $noticeId)
            ->where('event_key', 'notice')
            ->delete();

        return $result;
    }

    /**
     * @param Model $model
     * @param $key
     * @param $value
     */
    public static function createFrom(Model $model, $key, $value = null)
    {
        $type = strtolower(class_basename($model));
        $typeId = $model->id;
        $userId = $model->user_id;
        $value = is_null($value) ? $model->{$key} : $value;
        in_array($type, self::$types) && self::create($type, $typeId, $userId, $key, $value);
    }

    /**
     * @param $userId
     */
    public static function read($userId, $id)
    {
        Notification::userId($userId)->where('id', $id)->update(['read' => 1]);
    }

    /**
     * @param $userId
     */
    public static function readAll($userId)
    {
        Notification::userId($userId)->update(['read' => 1]);
    }
}
