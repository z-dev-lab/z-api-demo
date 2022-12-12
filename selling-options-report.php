<?php

$api_config = parse_ini_file("api_config.ini");

$property_address = $_REQUEST["property-address"];
$estimated_home_value = $_REQUEST["estimated-home-value"];
$home_condition = $_REQUEST["home-condition"];
$seller_agent_percentage = $_REQUEST["seller-agent-percentage"];
$buyer_agent_percentage = $_REQUEST["buyer-agent-percentage"];
$closing_costs = $_REQUEST["closing-costs"];
$instant_sale = !empty($_REQUEST["instant-sale"]);
$open_market = !empty($_REQUEST["open-market"]);
$modern_bridge = !empty($_REQUEST["modern-bridge"]);
$company_name = $_REQUEST["company-name"];
$agent_name = $_REQUEST["agent-name"];
$agent_email = $_REQUEST["agent-email"];
$submit_offer_request = !empty($_REQUEST["submit_offer_request"]);

function getToken() {

    global $api_config;

    //STEP 1: DEVELOPER CREDENTIALS
    #####################################################################
    $client_id = $api_config["client_id"];
    $client_secret = $api_config["client_secret"]; 
    $grant_type = $api_config["grant_type"];

    //Next step is to obtain OAuth2 access token. 
    //For this example, we will use two-legged OAuth by skipping the authorization flow and retrieving token using 
    //client_credentials grant type.


    //STEP 2: OBTAIN OAUTH2 ACCESS TOKEN
    ####################################
    // token URL
    $auth_url = $api_config["auth_url"];

    // get token using client_credentials grant
    $data = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' =>  $grant_type
    ];

    // Initializes a new cURL session
    $curl = curl_init($auth_url);

    // Set the CURLOPT_RETURNTRANSFER option to true
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Set the CURLOPT_POST option to true for POST request
    curl_setopt($curl, CURLOPT_POST, true);

    // Set the request data as JSON using json_encode function
    curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));

    // Set headers for Content-Type and Authorization
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    // Execute cURL request with all previous settings
    $auth_response = curl_exec($curl);

    // Close cURL session
    curl_close($curl);

    $token = json_decode($auth_response)->access_token;
    return $token;
}

function getEstimates() {

    global $api_config;
    global $company_name, $agent_name, $agent_email;
    global $property_address, $estimated_home_value, $home_condition;
    global $seller_agent_percentage,$buyer_agent_percentage, $closing_costs;
    global $instant_sale, $open_market, $modern_bridge;

    $name_array=explode(" ", $agent_name);
    $first_name = (count($name_array > 0) ? $name_array[0] : "");
    $last_name = (count($name_array > 1) ? $name_array[1] : "");

    // STEP 1: GET ACCESS TOKEN
    $access_token = getToken();

    //STEP 2: CALL ZAVVIE API USING THIS ACCESS TOKEN
    #################################################

    // ESTIMATES API
    $estimates_url = $api_config["estimates_url"];
    $api_key = $api_config["api_key"];

    $request = [
        'client-type'=> [
            'Seller'
        ],
        'agent-info'=> [
            'first-name'=> $first_name,
            'last-name'=> $last_name,
            'email-address'=> $agent_email,
            'brokerage'=> $company_name
        ],
        'selling-property'=> [
            "property-address" => $property_address,
            "estimated-home-value" => ["min"=>toNumber($estimated_home_value), "max"=>toNumber($estimated_home_value)],
            "home-condition" => $home_condition,
            "service-fee" => toNumber($service_fee),
            "seller-agent-percentage" => toNumber($seller_agent_percentage),
            "buyer-agent-percentage" => toNumber($buyer_agent_percentage)
        ]
    ];

    // Initializes a new cURL session
    $curl = curl_init($estimates_url);

    // Set the CURLOPT_RETURNTRANSFER option to true
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Set the CURLOPT_POST option to true for POST request
    curl_setopt($curl, CURLOPT_POST, true);

    // Set the request data as JSON using json_encode function
    curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($request));

    // Set headers for Content-Type and Authorization
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer '. $access_token,
        'zavvie-api-key: ' . $api_key,
        'Content-Type: application/json'
    ]);

    // Execute cURL request with all previous settings
    $response = curl_exec($curl);

    // Close cURL session
    curl_close($curl);

    return $response;
}

function submitOfferRequest() {
    global $api_config;
    global $company_name, $agent_name, $agent_email;
    global $property_address, $estimated_home_value, $home_condition;
    global $seller_agent_percentage,$buyer_agent_percentage, $closing_costs;
    global $instant_sale, $open_market, $modern_bridge;

    $name_array=explode(" ", $agent_name);
    $first_name = (count($name_array > 0) ? $name_array[0] : "");
    $last_name = (count($name_array > 1) ? $name_array[1] : "");

    list($street, $city, $statezip) = explode(", ", $property_address);
    list($state, $zip) = explode(" ", $statezip);

    $offers_requested=array();
    if ($open_market) $offers_requested[] = 'Open Market';
    if ($instant_sale) $offers_requested[] = 'Instant Sale';
    if ($modern_bridge) $offers_requested[] = 'Modern Bridge';

    // STEP 1: GET ACCESS TOKEN
    $access_token = getToken();

    //STEP 2: CALL ZAVVIE API USING THIS ACCESS TOKEN
    #################################################

    // SUBMIT API
    $offers_url = $api_config["offers_url"];
    $api_key = $api_config["api_key"];

    $request = [
        'client-type'=> [
            'Seller'
        ],
        'agent-info'=> [
            'first-name'=> $first_name,
            'last-name'=> $last_name,
            'email-address'=> $agent_email,
            'company-name'=> $company_name
        ],
        'offers-requested'=> $offers_requested,
        'selling-property'=> [
            'property'=> [
                'property-address'=> [
                    'street-address'=> $street,
                    'city'=> $city,
                    'state'=> $state,
                    'county'=> $county,
                    'zip-code'=> $zip
                ],
                "estimated-home-value" => toNumber($estimated_home_value),
                "home-condition" => $home_condition,
            ]
        ]
    ];

    // Initializes a new cURL session
    $curl = curl_init($offers_url);

    // Set the CURLOPT_RETURNTRANSFER option to true
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Set the CURLOPT_POST option to true for POST request
    curl_setopt($curl, CURLOPT_POST, true);

    // Set the request data as JSON using json_encode function
    curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($request));

    // update if previously submitted
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");

    // Set headers for Content-Type and Authorization
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer '. $access_token,
        'zavvie-api-key: ' . $api_key,
        'Content-Type: application/json'
    ]);

    // Execute cURL request with all previous settings
    $response = curl_exec($curl);

    // Close cURL session
    curl_close($curl);

    $tracking_id = json_decode($response)->{'tracking-id'};

    return $tracking_id;
}

function toNumber($str)
{
    return preg_replace("/([^0-9\\.])/i", "", $str);
}

if (empty ($property_address) || empty($estimated_home_value) || empty($home_condition)) exit("Make sure to complete the form before submitting");

if ($submit_offer_request) {
   $tracking_id = submitOfferRequest();
}
$estimates = json_decode(getEstimates(), true);
//echo $response["seller-solutions"]["open-market"]["estimated-open-market-price"]["min"];
//print_r($response);

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link rel="icon" href="assets/favicon.png" />
    <meta name="og:type" content="website" />
    <meta name="twitter:card" content="photo" />
    <link rel="stylesheet" type="text/css" href="css/zavvie-selling-options-report.css" />
    <link rel="stylesheet" type="text/css" href="css/styleguide.css" />
    <link rel="stylesheet" type="text/css" href="css/globals.css" />
    <link rel="stylesheet" type="text/css" href="assets/bootstrap.min.css" />
    <title>Selling Estimates</title>
  </head>
  <body style="margin: 0; background: #ffffff">
    <input type="hidden" id="anPageName" name="page" value="zavvie-selling-options-report" />
    <div class="container-center-horizontal">
      <div class="zavvie-selling-options-report screen">
        <div class="frame-1235-Umc8nY">
          <div class="frame-1223-VWejnG">
            <a href="index.html"><img class="sample-logo-7bCvAJ" src="img/sample-logo@2x.svg" /></a>
            <br/>
            <h2><label class="span0-u1xAsZ"><?php echo $company_name ?></label></h2>
            <div class="selling-options-prepared-by-abby-green-7bCvAJ avenir-medium-white-14px-2">
              <span class="span0-u1xAsZ">Selling Options<br /></span
              ><span class="span1-u1xAsZ">Prepared by: <?php echo $agent_name ?></span>
            </div>
          </div>
          <img class="line-2-VWejnG" src="img/line-2-3@1x.svg" />
        </div>
        <div class="frame-1234-Umc8nY">
          <div class="frame-1239-Y2mmJz">
            <div class="x3475-bear-creek-dr-boulder-co-80301-plvjFm avenir-heavy-normal-hunter-green-12px" id="property-address">
             <?php echo $property_address ?> 
            </div>
            <div class="frame-1238-plvjFm">
              <div class="powered-by-xvTxzE">Powered by</div>
              <img class="vector-xvTxzE" src="img/vector-4@2x.svg" />
            </div>
          </div>
          <div class="frame-1233-Y2mmJz">
            <div class="frame-1230-xNDQIY">
              <div class="frame-1237-k6A9Ap">
                <div class="open-market-sale-zy9emN avenir-heavy-normal-hunter-green-12px">Open Market Sale</div>
              </div>
              <div class="frame-1229-k6A9Ap border-1px-athens-gray-2">
                <div class="frame-1224-H6XBYq">
                  <div class="frame-1217-PC5TwH">
                    <div style="width:200px" class="estimated-open-market-price-oLcXap avenir-book-normal-chicago-10px">
                      Estimated Open Market Price
                    </div>
                    <div style="width:200px" class="x300000-oLcXap avenir-heavy-normal-hunter-green-14px" id="estimated-open-market-price"><?php echo  "$".number_format($estimates["seller-solutions"]["open-market"]["estimated-open-market-price"]["min"],0,'.', ',')?></div>
                  </div>
                  <div class="frame-1218-PC5TwH">
                    <div style="width:200px" class="cost-of-selling-KoWGhx avenir-book-normal-chicago-10px">Cost of Selling</div>
                    <div class="x27600-KoWGhx avenir-heavy-normal-hunter-green-14px" id="estimated-cost-of-selling"><?php echo  "$".number_format($estimates["seller-solutions"]["open-market"]["estimated-cost-of-selling"]["total-cost"]["min"],0,'.', ',')?></div>
                  </div>
                </div>
                <div class="frame-1228-H6XBYq">
                  <div class="frame-1221-VGTxxx">
                    <div class="applicable-to-all-properties-b61BxC avenir-light-hunter-green-8px">
                      Applicable to all properties
                    </div>
                    <div class="in-most-cases-will-m-b61BxC avenir-light-hunter-green-8px">
                      In most cases will maximize the seller’s net proceeds
                    </div>
                    <div class="frame-1222-b61BxC">
                      <div class="best-fit-for-sellers-N1wfZo avenir-regular-normal-hunter-green-8px">
                        <span class="span0-ZtEdvG avenir-regular-normal-hunter-green-8px">Best fit for:</span
                        ><span class="span1-ZtEdvG avenir-light-hunter-green-8px"
                          >&nbsp;&nbsp;Sellers whose top priority is maximizing the money they pocket from selling</span
                        >
                      </div>
                    </div>
                  </div>
                  <div class="frame-1227-VGTxxx">
                    <div class="frame-1225-87FKx9">
                      <div class="service-fee-00-T7lBCt avenir-heavy-normal-hunter-green-8px" id="service-fee-percentage">Service fee (<?php echo  number_format($estimates["seller-solutions"]["open-market"]["estimated-open-market-price"]["service-fee-percentage"]["min"],0,'.', ',')?>)</div>
                      <div class="seller-agent-30-T7lBCt avenir-heavy-normal-hunter-green-8px" id="seller-agent-percentage">Seller Agent (<?php echo  number_format($estimates["seller-solutions"]["open-market"]["estimated-cost-of-selling"]["seller-agent-percentage"]["min"],0,'.', ',')?>%)</div>
                      <div class="buyer-agent-30-T7lBCt avenir-heavy-normal-hunter-green-8px" id="buyer-agent-percentage">Buyer Agent (<?php echo  number_format($estimates["seller-solutions"]["open-market"]["estimated-cost-of-selling"]["buyer-agent-percentage"]["min"],0,'.', ',')?>%)</div>
                      <div class="prep-repairs-T7lBCt avenir-heavy-normal-hunter-green-8px">Prep &amp; Repairs</div>
                      <div class="closing-costs-T7lBCt avenir-heavy-normal-hunter-green-8px">Closing Costs</div>
                    </div>
                    <div class="frame-1226-87FKx9">
                      <div class="x0-97Xnxd avenir-light-hunter-green-8px" id="service-fee"><?php echo  "$".number_format($estimates["seller-solutions"]["open-market"]["estimated-cost-of-selling"]["service-fee"]["min"],0,'.', ',')?></div>
                      <div class="x9000-97Xnxd avenir-light-hunter-green-8px" id="seller-agent-commission"><?php echo  "$".number_format($estimates["seller-solutions"]["open-market"]["estimated-cost-of-selling"]["seller-agent-commission"]["min"],0,'.', ',')?></div>
                      <div class="x9000-5k0sZt avenir-light-hunter-green-8px" id="buyer-agent-commission"><?php echo  "$".number_format($estimates["seller-solutions"]["open-market"]["estimated-cost-of-selling"]["buyer-agent-commission"]["min"],0,'.', ',')?></div>
                      <div class="x6000-97Xnxd avenir-light-hunter-green-8px" id="prep-and-repairs"><?php echo  "$".number_format($estimates["seller-solutions"]["open-market"]["estimated-cost-of-selling"]["prep-and-repairs"]["min"],0,'.', ',')?></div>
                      <div class="x3600-97Xnxd avenir-light-hunter-green-8px" id="closing-costs"><?php echo  "$".number_format($estimates["seller-solutions"]["open-market"]["estimated-cost-of-selling"]["closing-costs"]["min"],0,'.', ',')?></div>
                    </div>
                  </div>
                </div>
                <div class="frame-1229-H6XBYq">
                  <div class="frame-1219-0T3ELV">
                    <div class="estimated-net-8xqRkx avenir-heavy-normal-hunter-green-10px">Estimated Net</div>
                    <div class="x272400-8xqRkx avenir-heavy-normal-hunter-green-14px" id="estimated-net"><?php echo  "$".number_format($estimates["seller-solutions"]["open-market"]["estimated-net"]["total-cost"]["min"],0,'.', ',')?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="frame-1234-Y2mmJz">
            <div class="frame-1230-FGkqEn">
              <div class="frame-1237-kK3ZvU">
                <div class="modern-bridge-SOgsCM avenir-heavy-normal-hunter-green-12px">Modern Bridge</div>
              </div>
              <div class="frame-1229-kK3ZvU border-1px-athens-gray-2">
                <div class="frame-1224-6Fu8Oc">
                  <div class="frame-1217-rbU6CW">
                    <div style="width:200px" class="estimated-bridge-option-price-is6x3S avenir-book-normal-chicago-10px">
                      Estimated Bridge Option Price
                    </div>
                    <div class="x295800-is6x3S avenir-heavy-normal-hunter-green-14px" id="estimated-bridge-offer-price"><?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-bridge-offer-price"]["min"],0,'.', ',')?></div>
                  </div>
                  <div class="frame-1218-rbU6CW">
                    <div class="cost-of-selling-rtMxTO avenir-book-normal-chicago-10px">Cost of Selling</div>
                    <div style="width:200px"class="x32539-38425-rtMxTO avenir-heavy-normal-hunter-green-14px" id="estimated-bridge-cost-of-selling"><?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["total-cost"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["total-cost"]["max"],0,'.', ',')?></div>
                  </div>
                </div>
                <div class="frame-1228-6Fu8Oc">
                  <div class="frame-1221-YWmThJ">
                    <div class="enables-a-seller-who-suMexm avenir-light-hunter-green-8px">
                      Enables a seller who is moving to first buy and move into a new home, then sell their current
                      house
                    </div>
                    <div class="removes-the-complexi-suMexm avenir-light-hunter-green-8px">
                      Removes the complexity and stress of coordinating two separate transactions
                    </div>
                    <div class="frame-1222-suMexm">
                      <div class="best-fit-for-sellers-xjWU7a avenir-regular-normal-hunter-green-8px">
                        <span class="span0-7Hu1nK avenir-regular-normal-hunter-green-8px">Best fit for:</span
                        ><span class="span1-7Hu1nK avenir-light-hunter-green-8px"
                          >&nbsp;&nbsp;Sellers who need the equity from their current house to purchase a new home</span
                        >
                      </div>
                    </div>
                  </div>
                  <div class="frame-1227-YWmThJ">
                    <div class="frame-1225-xoqgGa">
                      <div class="cost-of-home-being-purchased-cuwx6w avenir-heavy-normal-hunter-green-8px">
                        Cost of home being purchased
                      </div>
                      <div class="service-fee-300-499-cuwx6w avenir-heavy-normal-hunter-green-8px" id="bridge-service-fee-percentage">
                        Service fee (<?php echo  number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["service-fee-percentage"]["min"],0,'.', ',')?> - <?php echo  number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["service-fee-percentage"]["max"],0,'.', ',')?>%)
                      </div>
                      <div class="seller-agent-30-cuwx6w avenir-heavy-normal-hunter-green-8px" id="bridge-seller-agent-percentage">Seller Agent (<?php echo  number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["seller-agent-percentage"]["min"],0,'.', ',')?>%)</div>
                      <div class="buyer-agent-225-cuwx6w avenir-heavy-normal-hunter-green-8px" id="bridge-buyer-agent-percentage">Buyer Agent (<?php echo  number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["buyer-agent-percentage"]["min"],0,'.', ',')?>%)</div>
                      <div class="prep-repairs-cuwx6w avenir-heavy-normal-hunter-green-8px">Prep &amp; Repairs</div>
                      <div class="closing-costs-cuwx6w avenir-heavy-normal-hunter-green-8px">Closing Costs</div>
                    </div>
                    <div class="frame-1226-xoqgGa">
                      <div style="width:200px" class="x200000-300000-7xlSrS avenir-light-hunter-green-8px" id="bridge-total-cost"><?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["home-purchase-price"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["home-purchase-price"]["max"],0,'.', ',')?></div>
                      <div class="x8874-14760-7xlSrS avenir-light-hunter-green-8px" id="bridge-service-fee"><?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["service-fee"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["service-fee"]["max"],0,'.', ',')?></div>
                      <div class="x8874-7xlSrS avenir-light-hunter-green-8px"  id="bridge-seller-agent-commission"><?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["seller-agent-commission"]["min"],0,'.', ',')?></div>
                      <div class="x6656-7xlSrS avenir-light-hunter-green-8px" id="bridge-buyer-agent-commission"><?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["buyer-agent-commission"]["min"],0,'.', ',')?></div>
                      <div class="x8135-7xlSrS avenir-light-hunter-green-8px" id="bridge-prep-and-repairs"><?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["prep-and-repairs"]["min"],0,'.', ',')?></div>
                      <div class="x0-7xlSrS avenir-light-hunter-green-8px" id="bridge-closing-costs"><?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-cost-of-selling"]["closing-costs"]["min"],0,'.', ',')?></div>
                    </div>
                  </div>
                </div>
                <div class="frame-1230-6Fu8Oc">
                  <div class="frame-1219-blkIHl">
                    <div class="estimated-net-XPsNmo avenir-heavy-normal-hunter-green-10px">Estimated Net</div>
                    <div class="x272400-XPsNmo avenir-heavy-normal-hunter-green-14px" id="bridge-estimated-net"><?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-net"]["total-cost"]["min"],0,'.', ',')?></div>
                  </div>
                  <div class="frame-1220-blkIHl">
                    <div class="net-difference-EfR2ao avenir-heavy-normal-hunter-green-10px">Net Difference</div>
                    <div style="width:200px" class="x9139-15025-EfR2ao avenir-heavy-normal-hunter-green-14px" id="bridge-net-difference"><?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-net"]["net-difference"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["seller-solutions"]["modern-bridge"]["estimated-net"]["net-difference"]["max"],0,'.', ',')?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="frame-1235-Y2mmJz">
            <div class="frame-1230-MBIwNS">
              <div class="frame-1237-Vu99t1">
                <div class="instant-sale-offers-D3B47H avenir-heavy-normal-hunter-green-12px">Instant Sale Offers</div>
              </div>
              <div class="frame-1229-Vu99t1 border-1px-athens-gray-2">
                <div class="frame-1224-6nPSye">
                  <div class="frame-1217-uEDMcx">
                    <div class="estimated-instant-sale-price-Oc5xxk avenir-book-normal-chicago-10px">
                      Estimated Instant Sale Price
                    </div>
                    <div style="width:200px" class="x210000-270000-Oc5xxk avenir-heavy-normal-hunter-green-14px" id="estimated-instant-ofers-price"><?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-instant-offers-price"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-instant-offers-price"]["max"],0,'.', ',')?></div>
                  </div>
                  <div class="frame-1218-uEDMcx">
                    <div class="cost-of-selling-2QRMb2 avenir-book-normal-chicago-10px">Cost of Selling</div>
                    <div style="width:200px" class="x6300-8100-2QRMb2 avenir-heavy-normal-hunter-green-14px" id="estimated-instant-offers-cost-of-selling"><?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["total-cost"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["total-cost"]["max"],0,'.', ',')?></div>
                  </div>
                </div>
                <div class="frame-1228-6nPSye">
                  <div class="frame-1221-meqsWf">
                    <div class="sellers-can-skip-ope-73TlrJ avenir-light-hunter-green-8px">
                      Sellers can skip open houses and showings, and move out on their own timetable
                    </div>
                    <div class="frame-1222-73TlrJ">
                      <div class="best-fit-for-sellers-7xHEUU avenir-regular-normal-hunter-green-8px">
                        <span class="span0-vZKu5P avenir-regular-normal-hunter-green-8px">Best fit for:</span
                        ><span class="span1-vZKu5P avenir-light-hunter-green-8px"
                          >&nbsp;&nbsp;Sellers who want the certainty, speed, and convenience of getting a cash
                          offer</span
                        >
                      </div>
                    </div>
                  </div>
                  <div class="frame-1227-meqsWf">
                    <div class="frame-1225-z6B5Tw">
                      <div style="width:150px" class="service-fee-0-wwlkQ7 avenir-heavy-normal-hunter-green-8px" id="instant-offers-service-fee-percentage">Service fee (<?php echo  number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["service-fee-percentage"]["min"],0,'.', ',')?> - <?php echo  number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["service-fee-percentage"]["max"],0,'.', ',')?>%)</div>
                      <div style="width:150px" class="seller-agent-30-wwlkQ7 avenir-heavy-normal-hunter-green-8px" id="instant-offers-seller-agent-percentage">Seller Agent (<?php echo  number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["seller-agent-percentage"]["min"],0,'.', ',')?> - <?php echo  number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["seller-agent-percentage"]["max"],0,'.', ',')?>%)</div>
                      <div style="width:150px" class="buyer-agent-0-wwlkQ7 avenir-heavy-normal-hunter-green-8px" id="instant-offers-buyer-agent-percentage">Buyer Agent (<?php echo  number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["buyer-agent-percentage"]["min"],0,'.', ',')?> - <?php echo  number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["buyer-agent-percentage"]["max"],0,'.', ',')?>%)</div>
                      <div class="prep-repairs-wwlkQ7 avenir-heavy-normal-hunter-green-8px">Prep &amp; Repairs</div>
                      <div class="closing-costs-wwlkQ7 avenir-heavy-normal-hunter-green-8px">Closing Costs</div>
                    </div>
                    <div class="frame-1226-z6B5Tw">
                      <div style="width:200px" class="x0-0-7mZQWe avenir-light-hunter-green-8px" id="instant-offers-service-fee"><?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["service-fee"]["min"],0,'.', ',')?> -<?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["service-fee"]["max"],0,'.', ',')?></div>
                      <div style="width:200px"class="x6300-8100-7mZQWe avenir-light-hunter-green-8px" id="instant-offers-seller-agent-commission"><?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["seller-agent-commission"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["seller-agent-commission"]["max"],0,'.', ',')?></div>
                      <div style="width:200px" class="x0-0-XNAEqo avenir-light-hunter-green-8px" id="instant-offers-buyer-agent-commission"><?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["buyer-agent-commission"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["buyer-agent-commission"]["max"],0,'.', ',')?></div>
                      <div style="width:200px"class="x0-0-3Wm4ib avenir-light-hunter-green-8px" id="instant-offers-prep-and-repairs"><?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["prep-and-repairs"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["prep-and-repairs"]["max"],0,'.', ',')?></div>
                      <div style="width:200px" class="x0-0-0iTg7Z avenir-light-hunter-green-8px" id="instant-offers-closing-costs"><?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["closing-costs"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-cost-of-selling"]["closing-costs"]["max"],0,'.', ',')?></div>
                    </div>
                  </div>
                </div>
                <div class="frame-1230-6nPSye">
                  <div class="frame-1219-9t0sfA">
                    <div class="estimated-net-C6MvuC avenir-heavy-normal-hunter-green-10px">Estimated Net</div>
                    <div style="width:200px" class="x203700-261900-C6MvuC avenir-heavy-normal-hunter-green-14px" id="instant-offers-estimated-net"><?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-net"]["total-cost"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-net"]["total-cost"]["max"],0,'.', ',')?></div>
                  </div>
                  <div class="frame-1220-9t0sfA">
                    <div class="net-difference-3Bd2Cz avenir-heavy-normal-hunter-green-10px">Net Difference</div>
                    <div style="width:200px" class="x10500-68700-3Bd2Cz avenir-heavy-normal-hunter-green-14px" id="instant-offers-net-difference"><?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-net"]["net-difference"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["seller-solutions"]["instant-sale"]["estimated-net"]["net-difference"]["max"],0,'.', ',')?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="frame-1254-Umc8nY">
          <div style="width:500px">
    <span id="tid" class="badge badge-pill bg-light"></span>
          </div>
          <div class="frame-1214-gWgvCF">
            <a href="#" onclick="this.href=document.location.search+'&submit_offer_request=on';"><div id="get_offers" class="get-offers-from-zavvie-hvEnF2 avenir-medium-white-14px">Get Offers from zavvie</div></a>
          </div>
        </div>
      </div>
    </div>
    <script type="text/javascript">
        function getQueryParams(url) {
            const paramArr = url.slice(url.indexOf('?') + 1).split('&');
            const params = {};
            paramArr.map(param => {
                const [key, val] = param.split('=');
                params[key] = decodeURIComponent(val);
            })
            return params;
        }

        <?php if (!empty($tracking_id)) { ?>
        if (getQueryParams(document.location.search)["submit_offer_request"] == "on") {
            document.getElementById("get_offers").innerHTML = "✅ Request Submitted";
            document.getElementById("tid").innerHTML="Tracking ID: <?php echo $tracking_id; ?>";
            window.scrollTo(0, document.body.scrollHeight);
        }
       <?php }  ?>

    </script>
  </body>
</html>
