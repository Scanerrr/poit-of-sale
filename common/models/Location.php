<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use common\models\query\LocationQuery;

/**
 * This is the model class for table "location".
 *
 * @property int $id
 * @property string $prefix
 * @property string $name
 * @property int $region_id
 * @property string $email
 * @property string $phone
 * @property string $country
 * @property string $state
 * @property string $city
 * @property string $address
 * @property string $zip
 * @property string $tax_rate
 * @property int $status 0-disabled, 1-active
 * @property int $is_open 0-closed, 1-open
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Region $region
 * @property Inventory[] $inventories
 * @property LocationUser[] $locationUsers
 * @property LocationWorkHistory[] $locationWorkHistories
 * @property Order[] $orders
 */
class Location extends ActiveRecord
{

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'location';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            [['prefix', 'name', 'region_id', 'email'], 'required'],
            [['region_id', 'status'], 'integer'],
            [['is_open'], 'boolean'],
            [['tax_rate'], 'number', 'min' => 0],
            ['tax_rate', 'default', 'value' => 0],
            [['created_at', 'updated_at'], 'safe'],
            [['prefix', 'phone', 'country', 'state', 'city', 'address'], 'string', 'max' => 255],
            [['name', 'email'], 'string', 'max' => 64],
            [['zip'], 'string', 'max' => 5],
            [['prefix'], 'unique'],
            [['email'], 'unique'],
            [['region_id'], 'exist', 'skipOnError' => true, 'targetClass' => Region::class, 'targetAttribute' => ['region_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'prefix' => 'Prefix',
            'name' => 'Name',
            'region_id' => 'Region',
            'email' => 'Email',
            'phone' => 'Phone',
            'country' => 'Country',
            'state' => 'State',
            'city' => 'City',
            'address' => 'Address',
            'zip' => 'Zip',
            'tax_rate' => 'Tax Rate',
            'status' => 'Status',
            'is_open' => 'Is Open',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInventories()
    {
        return $this->hasMany(Inventory::class, ['location_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::class, ['id' => 'region_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocationUsers()
    {
        return $this->hasMany(LocationUser::class, ['location_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocationWorkHistories()
    {
        return $this->hasMany(LocationWorkHistory::class, ['location_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['location_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return LocationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LocationQuery(get_called_class());
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (!$insert && isset($changedAttributes['is_open'])) {
            LocationWorkHistory::saveHistory($this->id, Yii::$app->user->id, $this->is_open, 'location');
            if (!$this->is_open) {

                $locationUsers = LocationUser::find()->forLocation($this->id)->isWorking()->all();

                foreach ($locationUsers as $locationUser) {
                    $locationUser->is_working = 0;
                    $locationUser->save();
                }
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }
}
