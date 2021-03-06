<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\Customer;

/**
 * Create customer form
 */
class CreateCustomerForm extends Model
{
    public $firstname;
    public $lastname;
    public $email;
    public $phone;
    public $gender;
    public $country;
    public $state;
    public $city;
    public $address;
    public $zip;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['firstname', 'lastname', 'phone', 'email'], 'atLeastOne', 'skipOnEmpty' => false],
            [['gender'], 'in', 'range' => ['male', 'female']],
            [['firstname', 'lastname', 'phone', 'email', 'country', 'state', 'city', 'address'], 'string', 'max' => 255],
            [['firstname', 'lastname', 'phone', 'email', 'country', 'state', 'city', 'address', 'zip'], 'trim'],
            [['zip'], 'string', 'max' => 5],
        ];
    }

    public function atLeastOne($attribute)
    {
        if (empty($this->firstname) || empty($this->lastname) || empty($this->phone) || empty($this->email)) {
            $this->addError($attribute, 'At least 1 of the field must be filled up properly');
        }
    }

    /**
     * create customer.
     *
     * @return Customer|null if customer was (not) created.
     */
    public function create(): ?Customer
    {
        if (!$this->validate()) return null;

        $customer = new Customer();
        $customer->firstname = $this->firstname;
        $customer->lastname = $this->lastname;
        $customer->email = $this->email;
        $customer->phone = $this->phone;
        $customer->gender = $this->gender;
        $customer->added_by = Yii::$app->user->id;
        $customer->country = $this->country;
        $customer->state = $this->state;
        $customer->city = $this->city;
        $customer->address = $this->address;
        $customer->zip = $this->zip;

        return $customer->save() ? $customer : null;
    }
}
