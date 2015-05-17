<?php

namespace League\OAuth2\Client\Entity;

class User
{
    protected $uid;
    protected $nickname;
    protected $name;
    protected $firstName;
    protected $lastName;
    protected $email;
    protected $location;
    protected $description;
    protected $imageUrl;
    protected $urls;
    protected $gender;
    protected $locale;

    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $name
            ));
        }

        return $this->{$name};
    }

    public function __set($property, $value)
    {
        if (!property_exists($this, $property)) {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $property
            ));
        }

        $this->$property = $value;

        return $this;
    }

    public function __isset($name)
    {
        return (property_exists($this, $name));
    }

    public function getArrayCopy()
    {
        return [
            'uid' => $this->uid,
            'nickname' => $this->nickname,
            'name' => $this->name,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'location' => $this->location,
            'description' => $this->description,
            'imageUrl' => $this->imageUrl,
            'urls' => $this->urls,
            'gender' => $this->gender,
            'locale' => $this->locale,
        ];
    }

    public function exchangeArray(array $data)
    {
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            switch ($key) {
                case 'uid':
                    $this->uid = $value;
                    break;
                case 'nickname':
                    $this->nickname = $value;
                    break;
                case 'name':
                    $this->name = $value;
                    break;
                case 'firstname':
                    $this->firstName = $value;
                    break;
                case 'lastname':
                    $this->lastName = $value;
                    break;
                case 'email':
                    $this->email = $value;
                    break;
                case 'location':
                    $this->location = $value;
                    break;
                case 'description':
                    $this->description = $value;
                    break;
                case 'imageurl':
                    $this->imageUrl = $value;
                    break;
                case 'urls':
                    $this->urls = $value;
                    break;
                case 'gender':
                    $this->gender = $value;
                    break;
                case 'locale':
                    $this->locale = $value;
                    break;
            }
        }

        return $this;
    }
}
