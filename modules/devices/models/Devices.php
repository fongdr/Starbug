<?php
namespace Starbug\Devices;
use Starbug\Core\DevicesModel;
class Devices extends DevicesModel {

  function register($device) {
    $token = $device['token'];
    $platform = $device['platform'];
    $environment = $device['environment'];
    $uid = $this->user->userinfo("id");

    $user_agent = $this->request->getHeader('HTTP_USER_AGENT');
    $exists = $this->query()->condition("token", $token)
                ->condition("platform", $platform)->condition("environment", $environment)->count();
    if ($exists) {
      // If the token exists, update the owner and user_agent.
      $this->store(["owner" => $uid, "user_agent" => $user_agent], ["token" => $token, "environment" => $environment, "platform" => $platform]);
    } else {
      $this->store(["owner" => $uid, "token" => $token, "platform" => $platform, "environment" => $environment, "user_agent" => $user_agent]);
    }
  }
}
