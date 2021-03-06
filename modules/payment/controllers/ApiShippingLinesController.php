<?php
namespace Starbug\Payment;

use Starbug\Core\ApiController;
use Starbug\Core\IdentityInterface;

class ApiShippingLinesController extends ApiController {
  public $model = "shipping_lines";
  function __construct(IdentityInterface $user, Cart $cart) {
    $this->user = $user;
    $this->cart = $cart;
  }
  function admin() {
    $this->api->render("Admin");
  }
  function select() {
    $this->api->render("Select");
  }
  function cart() {
    $params = [];
    if (!$this->request->hasParameter("order")) {
      $params["order"] = $this->cart->get("id");
    }
    $this->api->render("ShippingLines", $params);
  }
  function order() {
    $this->api->render("ShippingLines");
  }
  function filterQuery($collection, $query, &$ops) {
    if (!$this->user->loggedIn("root") && !$this->user->loggedIn("admin")) {
      $query->open("access");
      $query->condition("shipping_lines.orders_id.token", $this->request->getCookie("cid"));
      $query->orCondition("shipping_lines.orders_id.owner", $this->user->userinfo("id"));
      $query->close();
    }
    return $query;
  }
}
