<?php
namespace App\Core;

class Validator
{

    public static function run($config, $data): array
    {
        $result = [];

        if( count($data) != count($config["inputs"]) ){
            $result[]="Formulaire modifié par user";
        }
        foreach ($config["inputs"] as $name=>$input){

            if(!isset($data[$name])){
                $result[]="Il manque des champs";
            }
            if(!empty($input["required"]) && empty($data[$name])){
                $result[]="Vous avez supprimé l'attribut required";
            }

            if (isset($input['min']) &&
                isset($input['max']) &&
                !self::checkSize($data[$name], $input['max'], $input['min']) ){
                $result[]="Entre ".$input['min']." et ".$input['max']." caracteres.";
            }

            if($input["type"]=="password" && !self::checkPassword($data[$name])){
                $result[]="Password incorrect";
            }elseif($input["type"]=="email"  && !self::checkEmail($data[$name])){
                $result[]="Email incorrect";
            }elseif($input["type"]=="radio"
                || $input["type"]=="checkbox"
                && !empty($data['name'])){

                $allValues = [];
                foreach ($config["inputs"][$name]['choice'] as $choice){
                    $allValues[] = $choice['value'];
                }
                if (!self::checkExistChoice($data[$name], $allValues)){
                    $result[]="choix incorrect";
                }
            }elseif($input["type"]=="file"){
                $result[]=  self::checkFile($data[$name]);
            }


        }
        return $result;
    }

    public static function checkPassword($pwd): bool
    {
        return strlen($pwd)>=8 && strlen($pwd)<=16
            && preg_match("/[a-z]/i", $pwd, $result)
            && preg_match("/[0-9]/", $pwd, $result);
    }

    public static function checkExistChoice($value, $allValues): bool
    {
        return in_array($value, $allValues);
    }

    public static function checkSize($value, $max, $min): bool
    {
        return strlen($value)>= $min && strlen($value) <= $max;
    }

    public static function checkEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function checkFile($file): string
    {
        if (isset($file['name'])) {
            $tailleMax = 2097152;
            $authExt = array('jpg','jpeg','png','svg');
            if($file['size'] <= $tailleMax) {
                $extensionUpload = strtolower(substr(strrchr($file['name'], '.'), 1));
                if(in_array($extensionUpload, $authExt)) {
                    $chemin = "file.".$extensionUpload;
                    $move = move_uploaded_file($file["tmp_name"], $chemin);
                    if (!$move) {
                        return "Error importing your fil";
                    }
                }else{
                    return "Format invalid";
                }
            }else{
                return "Invalid logo size";
            }

        }
    }
}