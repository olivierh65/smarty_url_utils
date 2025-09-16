<?php

require_once 'smarty_url_utils.civix.php';
// phpcs:disable
use CRM_SmartyUrlUtils_ExtensionUtil as E;
// phpcs:enable

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function smarty_url_utils_civicrm_config(&$config): void {
  _smarty_url_utils_civix_civicrm_config($config);

  $smarty = CRM_Core_Smarty::singleton();
  $smarty->registerPlugin('modifier', 'shorten', 'smarty_url_utils_shorten');
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function smarty_url_utils_civicrm_install(): void {
  _smarty_url_utils_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function smarty_url_utils_civicrm_enable(): void {
  _smarty_url_utils_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function smarty_url_utils_civicrm_preProcess($formName, &$form): void {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function smarty_url_utils_civicrm_navigationMenu(&$menu): void {
//  _smarty_url_utils_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _smarty_url_utils_civix_navigationMenu($menu);
//}

function smarty_url_utils_shorten($string, $title='', $keyword='') {
  $client = new Client();
  $yourlsurl = 'http://yourls.famh.fr/yourls-api.php?signature=1cf45368aa&action=shorturl&format=json';
  // $url=parse_url($string);
  // parse_str($url['query'], $query_url);
  
  $query['signature']='1cf45368aa';
  $query['action']='shorturl';
  $query['format']='json';
  // $query['url']=$url['scheme']. '://' . $url['host'].$url['path'].'?'.http_build_query($query_url);
  //decode html caracters encoded by civicrm
  $query['url']=str_replace(' ', '+', htmlspecialchars_decode($string));
  if (! empty($keyword)) {
    $yourlsurl .= '&keyword=' . urlencode($keyword);
    $query['keyword']=$keyword;
  }
  if (! empty($title)) {
    $yourlsurl .= '&title=' . urlencode($title);
    $query['title']=$title;
  }
  try {
    // $response = $client->get($yourlsurl);
    $response = $client->request('POST', 'http://yourls.famh.fr/yourls-api.php', [
      'form_params' => $query
    ]);
    $result = json_decode($response->getBody()->getContents(), true);
    if (! empty($result['shorturl'])) {
      return $result['shorturl'];
    }
    else {
      return '';
    }
  } catch (RequestException $e) {
    if ($e->getCode() == 400) {
      // check if url is already defined
      $result=json_decode($e->getResponse()->getBody()->getContents(), true);
      if (! empty($result['shorturl'])) {
        return $result['shorturl'];
      }
    }
    \Drupal::logger('smarty_url_utils')->error('Request Error for: ' . $e->getMessage());
    return '';
  }
}
