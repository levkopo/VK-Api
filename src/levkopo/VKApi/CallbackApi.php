<?php


namespace levkopo\VKApi;


use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Deprecated;

class CallbackApi extends VKApi {

    #[ArrayShape([
        "type"=>"string",
        "group_id"=>"int",
        "object"=>"array",
        "event_id"=>"string",
        "secret"=>"string"
    ])]
    public array $data;

    protected function __construct(string $token, bool $user, string $apiVersion,
                                ?string $secret, ?string $confirmKey) {
        parent::__construct($token, $user, $apiVersion);
        $this->data = json_decode(file_get_contents("php://input"), true,
            flags: JSON_THROW_ON_ERROR);

        if(isset($this->data['secret'])&&$secret!=null&&
            $this->data['secret']!==$secret) exit(400);

        if($confirmKey!=null){
            if($this->data['type']=="confirmation")
                echo $confirmKey;

            exit(200);
        }
    }

    #[Deprecated(reason: "Callback API not worked with user account",
        replacement: "%class%::group(%parametersList%)")]
    public static function user(string $token, string $apiVersion = "5.124"): self {
        return new self($token, true, $apiVersion, null, null);
    }

    public static function group(string $token,
                                 string $apiVersion = "5.124",
                                 string $confirmKey = null,
                                 string $secret = null): self {
        return new self($token, false, $apiVersion, $secret, $confirmKey);
    }

    public function __destruct() {
        echo "ok";
    }
}