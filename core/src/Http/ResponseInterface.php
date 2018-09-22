<?php
namespace Starbug\Core;

/**
 * ResponseInterface class
 */
interface ResponseInterface {
  public function getHeaders();
  public function setHeaders($headers = []);
  public function getHeader($name);
  public function setHeader($name, $value = null);
  public function getCookies();
  public function setCookies($cookies = []);
  public function getCookie($name);
  public function setCookie($name, $value = null);
  public function getCode();
  public function setCode($code);
  public function getContentType();
  public function setContentType($type);
  public function getCharset();
  public function setCharset($charset);
  public function getContent();
  public function setContent($content);
  public function getCallable();
  public function setCallable(callable $callable);
  public function getTheme();
  public function setTheme($theme);
  public function getTemplate();
  public function setTemplate($template);
  public function getLayout();
  public function setLayout($layout);
  public function getStyles();
  public function setStyles($styles = []);
  public function getStyle($name);
  public function setStyle($name, $value = null);
  public function getScripts();
  public function setScripts($scripts = []);
  public function getScript($name);
  public function setScript($name, $value = null);
  public function assign($key, $value = "");
  public function send();
  public function missing();
  public function forbidden();
  public function redirect($url);
  public function capture($template, $params = [], $options = []);
  public function js($mid);
}
