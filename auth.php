<?php

use Curl\Curl;
use Campo\UserAgent;
use Sunra\PhpSimple\HtmlDomParser;

require_once './vendor/autoload.php';
require_once './myModules/loguru.php';

class vkauth {

  private $login;
  private $password;
  private $cookies;

  /**
   * Авторизация
   * @param string $login Логин
   * @param string $password Пароль
   */
  public function __construct(string $login, string $password) {
    $this->login = $login;
    $this->password = $password;
    return $this;
  }

  /**
   * Войти в аккаунт
   * @return void
   */
  public function tryLogin() {
    $loguru = new loguru();
    $curl = new Curl();

    $curl->setUserAgent(UserAgent::random());
    $curl->setFollowLocation(true);
    $curl->setReferer('https://vk.com/');
    $curl->get("https://vk.com/login?u=2&to=/al_feed.php");

    $saveCookies = $curl->getResponseCookies();
    $curl->setCookies($saveCookies);
    $dom = HtmlDomParser::str_get_html($curl->rawResponse);

    $to = @$dom->find('input[name=to]', 1)->attr['value'];
    if (!isset($to) or empty($to))
      return $loguru->error('Не удалось найти to');
    else $loguru->debug($to);

    $iph = @$dom->find('input[name=ip_h]', 0)->attr['value'];
    if (!isset($iph) or empty($iph))
      return $loguru->error('Не удалось найти ip_h');
    else $loguru->debug($iph);

    $lgdomain = @$dom->find('input[name=lg_domain_h]', 0)->attr['value'];
    if (!isset($lgdomain) or empty($lgdomain))
      return $loguru->error('Не удалось найти lg_domain_h');
    else $loguru->debug($lgdomain);

    $curl->post('https://login.vk.com/?act=login', [
      "act" => "login",
      "role" => "al_frame",
      "expire" => "",
      "to" => $to,
      "recaptcha" => "",
      "captcha_sid" => "",
      "captcha_key" => "",
      "_origin" => "https://vk.com",
      "ip_h" => $iph,
      "lg_domain_h" => $lgdomain,
      "ul" => "",
      "email" => $this->login,
      "pass" => $this->password
    ]);

    $parse = mb_convert_encoding($curl->rawResponse, "utf-8", "windows-1251");

    $check = @explode("parent.onLoginDone('", $parse)[1];
    $check = @explode("', {name: '", $check)[0];

    if (is_null($check) or empty($check)) {
      $loguru->log('Не получилось войти в аккаунт');
      //! Save error
      if (!file_exists(__DIR__ . '/errors'))
        @mkdir(__DIR__ . '/errors');
      $this->saveAs($filename = 'errors/error' . count(glob(__DIR__ . '/errors/*')) . '.txt', $curl->rawResponse);
      $loguru->log('Ошибка, сохранил ошибку; ' . $filename);
    } else {
      $fullname = @explode("{name: '", $parse)[1];
      $fullname = @explode("',", $fullname)[0];
      $loguru->log("Вошли в аккаунт: {$check} ({$fullname})");
      $this->curl = $curl;
      $this->cookies = json_encode($curl->getResponseCookies());
    }
    return $this;
  }

  /**
   * Получает куки для дальнейших запросов
   * @return void
   */
  public function getCookies() {
    return $this->cookies;
  }

  /**
   * Сохраняет сессию вк
   * @return void
   */
  public function saveSession() {
    $this->saveAs('cookie_' . $this->login . '.json', json_encode($this->curl->getResponseCookies()));
  }

  /**
   * Просто сохраняет файлы
   * @param string $filename Название файла
  //  * @param mixed $out Контент, что будет в файле
   * @return void
   */
  public function saveAs(string $filename, $out) {
    file_put_contents(__DIR__ . "/" . $filename, $out);
  }
}
