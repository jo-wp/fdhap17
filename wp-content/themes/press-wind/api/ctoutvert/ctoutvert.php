<?php

class Ctoutvert
{
  public static function init() {}

  public static function connect_to_ctoutvert($soapCall = 'engine_returnFormInformations')
  {
    $wsdl = 'https://webservices.secureholiday.net/v2/engine.asmx?wsdl';
    $username = CTOUTVERT_USERNAME;
    $password = CTOUTVERT_PASSWORD;
    $id_engine = CTOUTVERT_ID_ENGINE;
    $isoLanguageCode = 'FR';

    try {
      $client = new SoapClient($wsdl, [
        'trace' => 1,
        'exceptions' => true
      ]);

      $params = [
        'user' => $username,
        'password' => $password,
        'idEngine' => $id_engine,
        'isoLanguageCode' => $isoLanguageCode,
      ];

      var_dump($params); // Debugging line to check parameters

      $result = $client->__soapCall($soapCall, [$params]);

      return $result;
    } catch (Exception $e) {
      error_log('Erreur appel ' . $soapCall . ' : ' . $e->getMessage());
      return null;
    }
  }

  public static function informations_ctoutvert()
  {
    $soapCall = 'engine_returnFormInformations';
    return self::connect_to_ctoutvert($soapCall);
  }

  public static function get_camping_ctoutvert($campingId = 125489)
  {
    $wsdl = 'https://webservices.secureholiday.net/v3/engine.asmx?wsdl';
    $username = 'redpanda';
    $password = 'MAf#$ma$kECQt';
    $id_engine = 702;

    try {
      $client = new SoapClient($wsdl, [
        'trace' => 1,
        'exceptions' => true
      ]);

      // Préparation de l’objet user
      $user = [
        'user' => $username,
        'password' => $password,
        'idEngine' => $id_engine
      ];

      // Préparation de ProductFilter
      $productFilter = [];
      if (!empty($product_types)) {
        $productFilter['ProductTypes'] = $product_types; // ex: ['all'] ou ['pitch']
      }
      if (!empty($key_list)) {
        $productFilter['KeyList'] = $key_list; // ex: [65258, 47895]
      }

      // Préparation de wsMoreInfoSettings
      $settings = [
        'Language' => 'FR',
        'EstablishmentFilter' => ['EstablishmentKey' => $campingId], // Filtre par l'ID du camping
      ];
      if (!empty($productFilter)) {
        $settings['ProductFilter'] = $productFilter;
      }

      // Construction du tableau de paramètres pour le SOAP call
      $params = [
        'user' => $user,
        'settings' => $settings
        // 'output' => ... // Tu peux ajouter ce param si besoin de filtrer la réponse
      ];

      // Appel SOAP
      $result = $client->__soapCall('GetEstablishmentInformations', [$params]);

      return $result;
    } catch (Exception $e) {
      error_log('Erreur appel GetEstablishmentInformations : ' . $e->getMessage());
      return $result = $e->getMessage();
    }
  }


  public static function ctoutvert_get_active_keys_from_engine()
  {
    $wsdl = 'https://webservices.secureholiday.net/v3/engine.asmx?wsdl';
    $username = 'redpanda';
    $password = 'MAf#$ma$kECQt';
    $id_engine = 1704;

    try {
      $client = new SoapClient($wsdl, [
        'trace' => 1,
        'exceptions' => true
      ]);

      // Objet user (cf. identification)
      $user = [
        'user' => $username,
        'password' => $password,
        'idEngine' => $id_engine
      ];

      // Paramètres pour la méthode
      $params = [
        'user' => $user
      ];

      // Appel de la méthode
      $result = $client->__soapCall('GetActiveKeysFromEngine', [$params]);

      return $result;
    } catch (Exception $e) {
      error_log('Erreur appel GetActiveKeysFromEngine : ' . $e->getMessage());
      return null;
    }
  }

  public static function ctoutvert_search_holidays($dateFilter = [], $productTypes = [], $onlyWithOffer = false)
  {
    $wsdl = 'https://webservices.secureholiday.net/v2/engine.asmx?wsdl';
    $username = CTOUTVERT_USERNAME;
    $password = CTOUTVERT_PASSWORD;
    $id_engine = CTOUTVERT_ID_ENGINE;

    try {
      $client = new SoapClient($wsdl, [
        'trace'      => 1,
        'exceptions' => true,
        // utile quand on envoie des listes
        'features'   => SOAP_SINGLE_ELEMENT_ARRAYS,
      ]);

      // Par sécurité, assure des dates valides si rien n’est passé
      if (empty($dateFilter)) {
        $dateFilter = [
          'startDate' => date('Y-m-d', strtotime('+1 day')),
          'endDate'   => date('Y-m-d', strtotime('+3 years')),
        ];
      }

      $params = [
        'user' => [
          'user'     => $username,
          'password' => $password,
          'idEngine' => $id_engine,
        ],
        'language'   => 'FR',
        // !!! clé correcte (singulier)
        // 'dateFilter' => $dateFilter,
      ];

      if ($onlyWithOffer) {
        // N’envoie pas les champs obsolètes
        $params['specialOfferFilter'] = [
          // 'ExcludeNonOffer'     => false,   // uniquement des séjours avec offre
          // 'IncludeClassicOffers'=> false,   // inclure les offres "classiques"
          'lastMinute' => true,
          'weekEnd' => true,
          'flash' => true,
          'exclusive' => true
          // 'offerTypes' => ['Classic', 'Injected'], // exemple si tu veux cibler
          // 'campaignWSCode' => 'XXX',              // optionnel
          // 'DiscountCode'   => 'PROMO2025',        // optionnel
        ];
      }

      // Optionnel : filtrer des types de produits
      if (!empty($productTypes)) {
        $params['productFilter'] = [
          'productTypes' => $productTypes, // vérifie le nom exact attendu par le WSDL
        ];
      }

      $result = $client->__soapCall('engine_returnAvailabilityAdvanced', [$params]);

      // DEBUG utile: vérifie ce qui a été réellement envoyé
      // error_log($client->__getLastRequest());

      return $result;
    } catch (Exception $e) {
      error_log('Erreur appel engine_returnAvailabilityAdvanced : ' . $e->getMessage());
      return $e->getMessage();
    }
  }



  public static function ctoutvert_get_specialoffer($campingId)
  {
    $wsdl      = 'https://webservices.secureholiday.net/v2/engine.asmx?wsdl';
    $username  = CTOUTVERT_USERNAME;
    $password  = CTOUTVERT_PASSWORD;
    $id_engine = (int) CTOUTVERT_ID_ENGINE;

    try {
      $client = new SoapClient($wsdl, [
        'trace'              => 1,
        'exceptions'         => true,
        'cache_wsdl'         => WSDL_CACHE_NONE,   // mets BOTH en prod
        'connection_timeout' => 15,
        'soap_version'       => SOAP_1_1,          // .asmx -> 1.1 en général
        'features'           => SOAP_SINGLE_ELEMENT_ARRAYS, // force les arrays 1 élément
      ]);

      // IMPORTANT : adapter la structure aux balises de ta REQUÊTE
      $params = [
        'user'             => $username,          // <web:user>XXX</web:user>
        'password'         => $password,          // <web:password>XXXXX</web:password>
        'idEstablishment'  => ['int' => [(int) $campingId]], // <web:idEstablishment><web:int>3200</web:int></web:idEstablishment>
        'idEngine'         => $id_engine,         // <web:idEngine>702</web:idEngine>
        'isoLanguageCode'  => 'FR',               // <web:isoLanguageCode>FR</web:isoLanguageCode>
      ];

      $response = $client->__soapCall('establishment_returnSpecialOffers', [$params]);

      return $response;
    } catch (SoapFault $e) {
      error_log('SoapFault establishment_returnSpecialOffers : ' . $e->faultcode . ' - ' . $e->getMessage());
      // Debug rapide (décommente si besoin)
      // error_log('LAST REQUEST: ' . $client->__getLastRequest());
      // error_log('LAST RESPONSE: ' . $client->__getLastResponse());
      return false;
    } catch (Exception $e) {
      error_log('Erreur appel establishment_returnSpecialOffers : ' . $e->getMessage());
      return false;
    }
  }

  public static function ctoutvert_get_discountcode($campingId)
  {
    $wsdl      = 'https://webservices.secureholiday.net/v2/engine.asmx?wsdl';
    $username  = CTOUTVERT_USERNAME;
    $password  = CTOUTVERT_PASSWORD;
    $id_engine = (int) CTOUTVERT_ID_ENGINE;

    try {
      $client = new SoapClient($wsdl, [
        'trace'              => 1,
        'exceptions'         => true,
        'cache_wsdl'         => WSDL_CACHE_NONE,   // mets BOTH en prod
        'connection_timeout' => 15,
        'soap_version'       => SOAP_1_1,          // .asmx -> 1.1 en général
        'features'           => SOAP_SINGLE_ELEMENT_ARRAYS, // force les arrays 1 élément
      ]);

      // IMPORTANT : adapter la structure aux balises de ta REQUÊTE
      $params = [
        'user'             => $username,          // <web:user>XXX</web:user>
        'password'         => $password,          // <web:password>XXXXX</web:password>
        'idEstablishment'  => ['int' => [(int) $campingId]], // <web:idEstablishment><web:int>3200</web:int></web:idEstablishment>
        'idEngine'         => $id_engine,         // <web:idEngine>702</web:idEngine>
        'isoLanguageCode'  => 'FR',               // <web:isoLanguageCode>FR</web:isoLanguageCode>
      ];

      $response = $client->__soapCall('GetStayWithDiscountCode', [$params]);

      return $response;
    } catch (SoapFault $e) {
      error_log('SoapFault establishment_returnSpecialOffers : ' . $e->faultcode . ' - ' . $e->getMessage());
      // Debug rapide (décommente si besoin)
      // error_log('LAST REQUEST: ' . $client->__getLastRequest());
      // error_log('LAST RESPONSE: ' . $client->__getLastResponse());
      return false;
    } catch (Exception $e) {
      error_log('Erreur appel establishment_returnSpecialOffers : ' . $e->getMessage());
      return false;
    }
  }

  public static function ctoutvert_get_discounts($campingId, $language = 'FR')
  {
    // ⚠️ À ADAPTER : mets ici le bon WSDL pour le service de pricing des offres spéciales
    $wsdl      = 'https://webservices.secureholiday.net/v2/engine.asmx?wsdl';
    $username  = CTOUTVERT_USERNAME;   // correspond à <tem:Login>
    $password  = CTOUTVERT_PASSWORD;   // correspond à <tem:Password>
    $id_engine = (int) CTOUTVERT_ID_ENGINE; // correspond à <tem:EngineId>

    try {
      $client = new SoapClient($wsdl, [
        'trace'              => 1,
        'exceptions'         => true,
        'cache_wsdl'         => WSDL_CACHE_NONE,
        'connection_timeout' => 15,
        'soap_version'       => SOAP_1_1,
        'features'           => SOAP_SINGLE_ELEMENT_ARRAYS,
      ]);

      // === HEADER SOAP (équivalent de ton XML) =========================
      // <soapenv:Header>
      //    <tem:Password>passwordtest</tem:Password>
      //    <tem:Login>usertest</tem:Login>
      //    <tem:EngineId>702</tem:EngineId>
      // </soapenv:Header>

      $ns = 'http://tempuri.org/';

      $headers = [
        new SoapHeader($ns, 'Password', $password, false),
        new SoapHeader($ns, 'Login', $username, false),
        new SoapHeader($ns, 'EngineId', $id_engine, false),
      ];

      $client->__setSoapHeaders($headers);

      // === BODY SOAP (équivalent de ton XML) ===========================
      // <soapenv:Body>
      //    <tem:SpecialOffersPricingParameter>
      //       <tem:EstablishmentId>14525</tem:EstablishmentId>
      //       <tem:Language>FR</tem:Language>
      //    </tem:SpecialOffersPricingParameter>
      // </soapenv:Body>

      $params = [
        'SpecialOffersPricingParameter' => [
          'EstablishmentId' => (int) $campingId,
          'Language'        => $language,
        ]
      ];

      // Le nom de la méthode SOAP (à vérifier dans le WSDL)
      $response = $client->__soapCall('SpecialOffersPricing', [$params]);

      // Debug si besoin :
      // error_log("REQUEST:\n" . $client->__getLastRequest());
      // error_log("RESPONSE:\n" . $client->__getLastResponse());

      return $response;
    } catch (SoapFault $e) {
      error_log('SoapFault SpecialOffersPricing : ' . $e->faultcode . ' - ' . $e->getMessage());
      // error_log("REQUEST:\n" . $client->__getLastRequest());
      // error_log("RESPONSE:\n" . $client->__getLastResponse());
      return false;
    } catch (Exception $e) {
      error_log('Erreur appel SpecialOffersPricing : ' . $e->getMessage());
      return false;
    }
  }
}


if (! is_admin() && ! (defined('WP_CLI') && WP_CLI)) {

  // $data = Ctoutvert::get_camping_ctoutvert(14166);
  // $data = Ctoutvert::ctoutvert_get_active_keys_from_engine();
  // $dateFilters = [
  //   'startDate' => '2026-08-05',
  //   'endDate' => '2026-08-15'
  // ];
  // $data = Ctoutvert::ctoutvert_search_holidays($dateFilters, null, true);
  // var_dump($data);
  // die();

  // $data = Ctoutvert::ctoutvert_get_specialoffer(7624);
  // foreach($data->establishment_returnSpecialOffersResult->establishmentsSpecialOfferList->establishmentSpecialOfferList[0]->SpecialOfferList->specialOffers->specialOffer as $item):
  //  echo '<pre>'; var_dump($item); echo '</pre>';
  // endforeach;
  // die();

  // $data = Ctoutvert::ctoutvert_get_specialoffer(7624);
  // echo '<pre>';
  // var_dump($data->establishment_returnSpecialOffersResult->establishmentsSpecialOfferList->establishmentSpecialOfferList[0]->SpecialOfferList);
  //var_dump($data->engine_returnAvailabilityAdvancedResult->availabilityInformationList->availabilityInformations[0]);
  // $i = 1;
  // foreach ($data->engine_returnAvailabilityAdvancedResult->availabilityInformationList->availabilityInformations as $item):
  //   echo $i;
  //   // var_dump($item->establishmentInformation);
  //   echo '<br/>';
  //   echo $item->establishmentInformation->establishmentId;
  //   echo '<br/>';
  //   echo $item->establishmentInformation->name;
  //   echo '<br/>';
  //   echo $item->establishmentInformation->postalCode;
  //   echo '<br/>';
  //   echo $item->establishmentInformation->town;
  //   echo '<hr>';
  //   $i++;
  // endforeach;
  // // echo '</pre>';
  // die();


  // die();
}
