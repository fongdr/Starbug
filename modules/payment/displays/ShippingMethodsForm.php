<?php

namespace Starbug\Payment;

use Starbug\Core\FormDisplay;

class ShippingMethodsForm extends FormDisplay {
  public $model = "shipping_methods";
  public $cancel_url = "admin/shipping_methods";
  function build_display($options) {
    $this->add("name");
    $this->add("description");
    $this->add(["shipping_rates", "input_type" => "text", "data-dojo-type" => "sb/form/CRUDList", "data-dojo-props" => "model:'shipping_rates', newItemLabel:'Add New Shipping Rate'"]);
    $this->add("position");
  }
}
?>