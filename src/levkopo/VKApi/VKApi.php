<?php


namespace levkopo\VKApi;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class VKApi {
    private string $token;
    private bool $user;
    private string $apiVersion;
    private HttpClientInterface $httpClient;

    protected function __construct(string $token, bool $user = false, string $apiVersion = "5.124") {
        $this->token = $token;
        $this->user = $user;
        $this->apiVersion = $apiVersion;
        $this->httpClient = HttpClient::createForBaseUri("https://api.vk.com/method/");
    }

    public function getUsers(array $userIds, array $fields = [], string $nameCase = ""): array|false {
        return $this->request("users.get", [
            "user_ids"=>implode(",", $userIds),
            "fields"=>implode(",", $fields),
            "name_case"=>$nameCase
        ]);
    }

    public function getUser(int $userId, array $fields = [], string $nameCase = ""):array|false {
        return $this->getUsers([$userId], $fields, $nameCase)[0]??false;
    }

    public function getGroups(array $groupsIds, array $fields = []): array|false {
        return $this->request("groups.getById", [
            "group_ids"=>implode(",", $groupsIds),
            "fields"=>implode(",", $fields),
        ]);
    }

    public function getGroup(int $groupId = null, array $fields = []):array|false {
        if($groupId==null&&$this->user) return false;
        return $this->getGroups($groupId!=null?[$groupId]:[], $fields)[0]??false;
    }

    public function request(string $method, array $parameters = []): array|int|false {
        $parameters["access_token"] = $this->token;
        $parameters["v"] = $this->apiVersion;

        try {
            $response = $this->httpClient->request("GET", $method."?".http_build_query($parameters));
            if($response->getStatusCode()!=200)
                return false;

            $response = json_decode($response->getContent(true), true, flags: JSON_THROW_ON_ERROR);
            if(isset($response['error'])) {
                var_dump($response);
                return false;
            }

            return $response['response']??$response;
        } catch (Throwable) {
            return false;
        }
    }

    public function editMessage(int $peerId,
            int $messageId,
            string $message = "",
            array $attachments = [],
            bool $keepForwardMessages = true,
            bool $keepSnippets = true,
            array $params = []): int|false {
        return $this->request("messages.edit", $params + [
                "peer_id"=>$peerId,
                ($this->user?"message_id":"conversation_message_id") => $messageId,
                "message"=>$message,
                "attachment"=>implode(",", $attachments),
                "random_id"=>0,
                "keep_forward_messages"=>$keepForwardMessages?1:0,
                "keep_snippets"=>$keepSnippets?1:0,
            ]);
    }

    public function sendMessage(int $peerId,
                                string $message = "",
                                array $attachments = [],
                                int $replyTo = null,
                                array $forwardMessages = [],
                                ?string $payload = "",
                                array $params = []): int|false{
        $response =  $this->request("messages.send", $params + [
                ($this->user?"peer_id":"peer_ids")=>$peerId,
                "message"=>$message,
                "attachment"=>implode(",", $attachments),
                "payload"=>$payload,
                "random_id"=>0
            ] + (empty($forwardMessages)?[]:[
                "forward_messages"=>implode(",", $forwardMessages)
            ]) + ($replyTo==null?[]:[
                    "reply_to"=>$replyTo
                ]));

        if(!$this->user){
            $response = $response[0]["conversation_message_id"]??0;
        }

        return $response;
    }

    public static function user(string $token, string $apiVersion = "5.124"): self {
        return new self($token, true, $apiVersion);
    }

    public static function group(string $token, string $apiVersion = "5.124"): self {
        return new self($token, false, $apiVersion);
    }
}
