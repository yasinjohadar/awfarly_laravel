<?php
/*
 * This trait used to encrypt/decrypt the attributes in the database.
*/

namespace App\Http\Traits;

use Illuminate\Support\Facades\Crypt;

trait EncryptableTrait
{
    /**
     * get attribute
     * @param $key
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable)) {
            $value = Crypt::decrypt($value);
        }
    }

    /**
     * set attribute
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable)) {
            $value = Crypt::encrypt($value);
        }

        return parent::setAttribute($key, $value);
    }
}
