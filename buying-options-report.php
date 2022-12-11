<?php

$api_config = parse_ini_file("api_config.ini");

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
$buying_location = $_REQUEST["buying-location"];
$home_purchase_price_min = $_REQUEST["home-purchase-price-min"];
$home_purchase_price_max = $_REQUEST["home-purchase-price-max"];
$down_payment_min = $_REQUEST["down-payment-min"];
$down_payment_max = $_REQUEST["down-payment-max"];
$down_payment_percentage_min = $_REQUEST["down-payment-percentage-min"];
$down_payment_percentage_max = $_REQUEST["down-payment-percentage-max"];
$closing_costs = $_REQUEST["closing-costs"];
$closing_costs_percentage = $_REQUEST["closing-costs-percentage"];
$mortgage_financing = !empty($_REQUEST["mortgage-financing"]);
$cash_offer = !empty($_REQUEST["cash-offer"]);
$homeownership_accelerator = !empty($_REQUEST["homeownership-accelerator"]);
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
    global $buying_location, $home_purchase_price_min, $home_purchase_price_max, $closing_costs;
    global $down_payment_percentage_min, $down_payment_percentage_max;
    global $mortgage_financing, $cash_offer, $homeownership_accelerator;

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
            'Buyer'
        ],
        'agent-info'=> [
            'first-name'=> $first_name,
            'last-name'=> $last_name,
            'email-address'=> $agent_email,
            'company-name'=> $company_name
        ],
        'buying-property'=> [
            "home-location" => $buying_location,
            "home-purchase-price" => ["min"=>toNumber($home_purchase_price_min), "max"=>toNumber($home_purchase_price_max)],
            "down-payment" => ["min"=>toNumber($down_payment_percentage_min), "max"=>toNumber($down_payment_percentage_max)],
            "closing-costs" => toNumber($closing_costs)
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
    global $buying_location, $home_purchase_price_min, $home_purchase_price_max;
    global $down_payment_min, $down_payment_max;
    global $mortgage_financing, $cash_offer, $homeownership_accelerator;

    $name_array=explode(" ", $agent_name);
    $first_name = (count($name_array > 0) ? $name_array[0] : "");
    $last_name = (count($name_array > 1) ? $name_array[1] : "");
    list($county, $state) = explode(", ", $buying_location);

    $offers_requested=array();
    if ($mortgage_financing) $offers_requested[] = 'Mortgage Financing';
    if ($cash_offer) $offers_requested[] = 'Cash Offer';
    if ($homeownership_accelerator) $offers_requested[] = 'Homeownership Accelerator';

    // STEP 1: GET ACCESS TOKEN
    $access_token = getToken();

    //STEP 2: CALL ZAVVIE API USING THIS ACCESS TOKEN
    #################################################

    // SUBMIT API
    $offers_url = $api_config["offers_url"];
    $api_key = $api_config["api_key"];

    $request = [
        'client-type'=> [
            'Buyer'
        ],
        'agent-info'=> [
            'first-name'=> $first_name,
            'last-name'=> $last_name,
            'email-address'=> $agent_email,
            'company-name'=> $company_name
        ],
        'offers-requested'=> $offers_requested,
        "buying-property"=>[
            "buyer-info"=>[
                "max-offer-price"=>toNumber($home_purchase_price_max),
                "down-payment"=>toNumber($down_payment_max)
            ],
            "buying-locations"=>[
                 [
                     "state"=>$state,
                     "county"=>trimCounty($county)
                 ]
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

function trimCounty($county) {
   if ($county) {
       $county=strtolower($county);
       $filters = ["county", "parish"];//, "borough"];
       foreach ($filters as $filter) {
           $index = strpos($county, $filter);
           if ($index) {
               $county = trim(substr($county, 0, $index));
               break;
           }
       }
   }
   return $county;
}

if (empty ($buying_location) || empty($home_purchase_price_min))  exit("Make sure to complete the form before submitting");

if ($submit_offer_request) {
   $tracking_id = submitOfferRequest();
}

$estimates = json_decode(getEstimates(), true);

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link rel="icon" href="assets/favicon.png" />
    <meta name="og:type" content="website" />
    <meta name="twitter:card" content="photo" />
    <link rel="stylesheet" type="text/css" href="css/zavvie-buying-options-report.css" />
    <link rel="stylesheet" type="text/css" href="css/styleguide.css" />
    <link rel="stylesheet" type="text/css" href="css/globals.css" />
    <link rel="stylesheet" type="text/css" href="assets/bootstrap.min.css" />
    <title>Buying Estimates</title>
  </head>
  <body style="margin: 0; background: #ffffff">
    <input type="hidden" id="anPageName" name="page" value="zavvie-buying-options-report" />
    <div class="container-center-horizontal">
      <div class="zavvie-buying-options-report screen">
        <div class="frame-1235-EMkVDE">
          <div class="frame-1223-OQI1ts">
            <img class="sample-logo-Za085G" src="img/sample-logo@2x.svg" />
            <br/>
            <h2><label class="span0-7m35rp"><?php echo $company_name ?></label></h2>
            <div class="buying-options-prepared-by-abby-green-Za085G avenir-medium-white-14px-2">
              <span class="span0-7m35rp">Buying Options<br /></span
              ><span class="span1-7m35rp">Prepared by: <?php echo $agent_name ?></span>
            </div>
          </div>
          <img class="line-2-OQI1ts" src="img/line-2-3@1x.svg" />
        </div>
        <div class="frame-1240-EMkVDE">
          <div class="x3475-bear-creek-dr-boulder-co-80301-LmkWNO avenir-heavy-normal-hunter-green-12px" id="home-location">
<?php echo $home_location ?>
          </div>
          <div class="frame-1238-LmkWNO">
            <div class="powered-by-dqWuSE">Powered by</div>
            <img class="vector-dqWuSE" src="img/vector-6@2x.svg" />
          </div>
        </div>
        <div class="frame-1248-EMkVDE">
          <div class="frame-1237-28vPPx">
            <div class="mortgage-financing-QYlPyj avenir-heavy-normal-hunter-green-12px">Mortgage Financing</div>
          </div>
          <div class="frame-1244-28vPPx">
            <div class="frame-1217-vrgSoh">
              <div class="home-purchase-value-Q75rp6 avenir-book-normal-chicago-10px">Home Purchase Value</div>
              <div style="width:500px" class="x400000-500000-Q75rp6 avenir-heavy-normal-hunter-green-14px" id="home_purchase_value"><?php echo "$".number_format(toNumber($home_purchase_price_min),0,'.', ',') ?>
 - <?php echo "$".number_format(toNumber($home_purchase_price_max),0,'.', ',') ?></div>
            </div>
            <div class="frame-1243-vrgSoh">
              <div class="frame-1242-kHdZxb">
                <div class="traditional-open-mar-XRBFXQ avenir-light-hunter-green-8px">
                  Traditional open market purchase with a mortgage lender.
                </div>
                <div class="often-the-most-cost-XRBFXQ avenir-light-hunter-green-8px">
                  Often the most cost effective way to buy a home
                </div>
                <div class="many-options-availab-XRBFXQ avenir-light-hunter-green-8px">
                  Many options available depending on the buyers needs and goals
                </div>
                <div class="best-fit-for-buyers-XRBFXQ avenir-regular-normal-hunter-green-8px">
                  <span class="span0-fmVBIJ avenir-regular-normal-hunter-green-8px">Best fit for:</span
                  ><span class="span1-fmVBIJ avenir-light-hunter-green-8px"
                    >&nbsp;&nbsp;Buyers with good credit and savings</span
                  >
                </div>
              </div>
              <div class="frame-1241-kHdZxb">
                <div class="eligibility-what-you-need-to-qualify-hwxeaP avenir-heavy-normal-hunter-green-8px">
                  Eligibility: what you need to qualify
                </div>
                <div class="credit-score-greater-than-620-hwxeaP avenir-light-hunter-green-8px">
                  Credit score greater than 620
                </div>
                <div class="x3-down-payment-hwxeaP avenir-light-hunter-green-8px">3% Down Payment</div>
                <div class="debt-to-income-ratio-45-hwxeaP avenir-light-hunter-green-8px">Debt to Income Ratio 45%</div>
              </div>
            </div>
          </div>
          <div class="frame-1247-28vPPx border-1px-athens-gray-2">
            <div class="frame-1227-xTz13V">
              <div class="frame-1225-va0u5m">
                <div class="down-payment-30-20-8CU6xQ avenir-heavy-normal-hunter-green-8px" id="down-payment-percentage">
                  Down Payment (<?php echo  number_format($estimates["buyer-solutions"]["mortgage-financing"]["cost-of-home-purchase"]["down-payment"] ["min-percentage"],0,'.', ',')?>% - <?php echo  number_format($estimates["buyer-solutions"]["mortgage-financing"]["cost-of-home-purchase"]["down-payment"] ["max-percentage"],0,'.', ',')?>%)
                </div>
                <div class="closing-costs-8CU6xQ avenir-heavy-normal-hunter-green-8px">Closing Costs</div>
              </div>
              <div class="frame-1226-va0u5m">
                <div style="width:300px" class="x12000-100000-kOyxt8 avenir-light-hunter-green-8px" id="down-payment"><?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["cost-of-home-purchase"]["down-payment"] ["min"],0,'.', ',')?> -  <?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["cost-of-home-purchase"]["down-payment"] ["max"],0,'.', ',')?></div>
                <div class="x3560-6450-kOyxt8 avenir-light-hunter-green-8px" id="closing-costs"><?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["cost-of-home-purchase"]["closing-costs"] ["min"],0,'.', ',')?> -<?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["cost-of-home-purchase"]["closing-costs"] ["max"],0,'.', ',')?> </div>
              </div>
            </div>
            <div class="frame-1246-xTz13V">
              <div class="frame-1245-BbixWl">
                <div class="mortgage-principal-interest-9BAxw1 avenir-heavy-normal-hunter-green-8px">
                  Mortgage (Principal &amp; Interest)
                </div>
                <div class="x1592-1728-9BAxw1 avenir-light-hunter-green-8px" id="mortgage-costs"><?php echo  "$".number_format(min([toNumber($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["mortgage"]["min"]),toNumber($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["mortgage"]["max"])]),0,'.', ',')?> - <?php echo  "$".number_format(max([toNumber($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["mortgage"]["min"]),toNumber($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["mortgage"]["max"])]),0,'.', ',')?></div>
              </div>
              <div class="frame-1227-BbixWl">
                <div class="frame-1225-xpmP2g">
                  <div class="taxes-QLF9nk avenir-heavy-normal-hunter-green-8px">Taxes</div>
                  <div class="insurance-QLF9nk avenir-heavy-normal-hunter-green-8px">Insurance</div>
                  <div class="utilities-QLF9nk avenir-heavy-normal-hunter-green-8px">Utilities</div>
                  <div class="maintenance-QLF9nk avenir-heavy-normal-hunter-green-8px">Maintenance</div>
                </div>
                <div class="frame-1226-xpmP2g">
                  <div style="width:300px" class="x203-592-aW3pal avenir-light-hunter-green-8px" id="taxes"><?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["taxes"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["taxes"]["max"],0,'.', ',')?></div>
                  <div class="x88-160-aW3pal avenir-light-hunter-green-8px" id="insurance"><?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["insurance"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["insurance"]["max"],0,'.', ',')?></div>
                  <div class="x96-170-aW3pal avenir-light-hunter-green-8px" id="utilities"><?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["utilities"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["utilities"]["max"],0,'.', ',')?></div>
                  <div class="x148-235-aW3pal avenir-light-hunter-green-8px" id="maintenance"><?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["maintenance"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["maintenance"]["max"],0,'.', ',')?></div>
                </div>
              </div>
            </div>
          </div>
          <div class="frame-1224-28vPPx">
            <div class="frame-1218-PbgLg7">
              <div style="width:300px" class="costs-of-home-purchase-Vo5ZDO avenir-heavy-normal-hunter-green-10px">
                Costs of Home Purchase
              </div>
              <div style="width:300px" class="x15560-106450-Vo5ZDO avenir-heavy-normal-hunter-green-14px" id="cost-of-home-purchase"><?php echo "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["cost-of-home-purchase"]["total-cost"]["min"],0,'.', ',')?> - <?php echo "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["cost-of-home-purchase"]["total-cost"]["max"],0,'.', ',')?></div>
            </div>
            <div class="frame-1219-PbgLg7">
              <div class="monthly-costs-zJWXxh avenir-heavy-normal-hunter-green-10px">Monthly Costs</div>
              <div style="width:300px" class="x2127-2885-zJWXxh avenir-heavy-normal-hunter-green-14px" id="monthly-costs"><?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["total-cost"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["total-cost"]["max"],0,'.', ',')?></div>
            </div>
          </div>
        </div>
        <div class="frame-1249-EMkVDE">
          <div class="frame-1237-r9ofoY">
            <div class="cash-offer-JqtFHl avenir-heavy-normal-hunter-green-12px">Cash Offer</div>
          </div>
          <div class="frame-1244-r9ofoY">
            <div class="frame-1217-AVe4nG">
              <div class="home-purchase-value-I4vFw7 avenir-book-normal-chicago-10px">Home Purchase Value</div>
              <div style="width:500px" class="x400000-500000-I4vFw7 avenir-heavy-normal-hunter-green-14px" id="home-purchase-price"><?php echo "$".number_format(toNumber($home_purchase_price_min),0,'.', ',') ?>
 - <?php echo "$".number_format(toNumber($home_purchase_price_max),0,'.', ',') ?></div>
            </div>
            <div class="frame-1251-AVe4nG">
              <div class="frame-1243-UDPS5D">
                <div class="frame-1242-srUx54">
                  <div class="turn-your-offer-into-tU8Be9 avenir-light-hunter-green-8px">
                    Turn your offer into a non-contingent cash offer
                  </div>
                  <div class="a-great-solution-for-tU8Be9 avenir-light-hunter-green-8px">
                    A great solution for competitive markets where multiple offer situations are common
                  </div>
                  <div class="can-help-buyers-purc-tU8Be9 avenir-light-hunter-green-8px">
                    Can help buyers purchase their home more quickly
                  </div>
                  <div class="best-fit-for-any-buy-tU8Be9 avenir-regular-normal-hunter-green-8px">
                    <span class="span0-4yznRY avenir-regular-normal-hunter-green-8px">Best fit for:</span
                    ><span class="span1-4yznRY avenir-light-hunter-green-8px"
                      >&nbsp;&nbsp;Any buyer looking for a competitive edge in the marketplace.</span
                    >
                  </div>
                </div>
              </div>
              <div class="frame-1241-UDPS5D">
                <div class="eligibility-what-you-need-to-qualify-NzzIvP avenir-heavy-normal-hunter-green-8px">
                  Eligibility: what you need to qualify
                </div>
                <div class="minimum-700-credit-score-NzzIvP avenir-light-hunter-green-8px">
                  Minimum 700 credit score
                </div>
                <div class="loan-pre-approval-NzzIvP avenir-light-hunter-green-8px">Loan pre-approval</div>
                <div class="deposit-of-25-of-new-home-purchase-price-NzzIvP avenir-light-hunter-green-8px">
                  Deposit of 2.5% of new home purchase price
                </div>
              </div>
            </div>
          </div>
          <div class="frame-1247-r9ofoY border-1px-athens-gray-2">
            <div class="frame-1227-x1kQu5">
              <div class="frame-1225-fxxVtY">
                <div class="down-payment-50-20-JefvHi avenir-heavy-normal-hunter-green-8px" id="down-payment-percentage">
                  Down Payment (<?php echo  number_format($estimates["buyer-solutions"]["cash-offer"]["cost-of-home-purchase"]["down-payment"] ["min-percentage"],0,'.', ',')?>% - <?php echo  number_format($estimates["buyer-solutions"]["cash-offer"]["cost-of-home-purchase"]["down-payment"] ["max-percentage"],0,'.', ',')?>%)
                </div>
                <div class="closing-costs-JefvHi avenir-heavy-normal-hunter-green-8px">Closing Costs</div>
                <div class="service-fee-JefvHi avenir-heavy-normal-hunter-green-8px">Service Fee</div>
              </div>
              <div class="frame-1226-fxxVtY">
                <div class="x20000-100000-7ZKKn5 avenir-light-hunter-green-8px" id="down-payment"><?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["cost-of-home-purchase"]["down-payment"] ["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["cost-of-home-purchase"]["down-payment"] ["max"],0,'.', ',')?></div>
                <div class="x3560-6450-7ZKKn5 avenir-light-hunter-green-8px" id="closing-costs"><?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["cost-of-home-purchase"]["closing-costs"] ["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["cost-of-home-purchase"]["closing-costs"] ["max"],0,'.', ',')?></div>
                <div class="x4000-5000-7ZKKn5 avenir-light-hunter-green-8px" id="service-fee"><?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["cost-of-home-purchase"]["service-fee"] ["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["cost-of-home-purchase"]["service-fee"] ["max"],0,'.', ',')?></div>
              </div>
            </div>
            <div class="frame-1246-x1kQu5">
              <div class="frame-1245-9g82xM">
                <div class="mortgage-principal-interest-je7pI6 avenir-heavy-normal-hunter-green-8px">
                  Mortgage (Principal &amp; Interest)
                </div>
                <div class="x1559-1728-je7pI6 avenir-light-hunter-green-8px" id="mortgage"><?php echo  "$".number_format(min([toNumber($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["mortgage"]["min"]),toNumber($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["mortgage"]["max"])]),0,'.', ',')?> - <?php echo  "$".number_format(max([toNumber($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["mortgage"]["min"]),toNumber($estimates["buyer-solutions"]["mortgage-financing"]["monthly-costs"] ["mortgage"]["max"])]),0,'.', ',')?></div>
              </div>
              <div class="frame-1227-9g82xM">
                <div class="frame-1225-I8yy9q">
                  <div class="taxes-jbecwx avenir-heavy-normal-hunter-green-8px">Taxes</div>
                  <div class="insurance-jbecwx avenir-heavy-normal-hunter-green-8px">Insurance</div>
                  <div class="utilities-jbecwx avenir-heavy-normal-hunter-green-8px">Utilities</div>
                  <div class="maintenance-jbecwx avenir-heavy-normal-hunter-green-8px">Maintenance</div>
                </div>
                <div class="frame-1226-I8yy9q">
                  <div style="width:300px" class="x203-592-qyppHg avenir-light-hunter-green-8px" id="taxes"><?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["monthly-costs"] ["taxes"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["monthly-costs"] ["taxes"]["max"],0,'.', ',')?></div>
                  <div class="x95-160-qyppHg avenir-light-hunter-green-8px" id="insurance"><?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["monthly-costs"] ["insurance"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["monthly-costs"] ["insurance"]["max"],0,'.', ',')?></div>
                  <div class="x96-170-qyppHg avenir-light-hunter-green-8px" id="utilities"><?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["monthly-costs"] ["utilities"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["monthly-costs"] ["utilities"]["max"],0,'.', ',')?></div>
                  <div class="x148-235-qyppHg avenir-light-hunter-green-8px" id="maintenance"><?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["monthly-costs"] ["maintenance"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["monthly-costs"] ["maintenance"]["max"],0,'.', ',')?></div>
                </div>
              </div>
            </div>
          </div>
          <div class="frame-1224-r9ofoY">
            <div class="frame-1218-OwQsdL">
              <div class="costs-of-home-purchase-dOkWz6 avenir-heavy-normal-hunter-green-10px">
                Costs of Home Purchase
              </div>
              <div style="width:300px" class="x27560-111450-dOkWz6 avenir-heavy-normal-hunter-green-14px" id="cost-of-home-purchase"><?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["cost-of-home-purchase"]["total-cost"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["cost-of-home-purchase"]["total-cost"]["max"],0,'.', ',')?></div>
            </div>
            <div class="frame-1219-OwQsdL">
              <div class="monthly-costs-HTVpda avenir-heavy-normal-hunter-green-10px">Monthly Costs</div>
              <div style="width:300px" class="x2101-2885-HTVpda avenir-heavy-normal-hunter-green-14px" id="monthly-costs"><?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["monthly-costs"] ["total-cost"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["cash-offer"]["monthly-costs"] ["total-cost"]["max"],0,'.', ',')?></div>
            </div>
          </div>
        </div>
        <div class="frame-1250-EMkVDE">
          <div class="frame-1237-EfnMPh">
            <div class="homeownership-accelerator-E0E9mf avenir-heavy-normal-hunter-green-12px">
              Homeownership Accelerator
            </div>
          </div>
          <div class="frame-1244-EfnMPh">
            <div class="frame-1217-PtFVXx">
              <div class="home-purchase-value-Tm1siW avenir-book-normal-chicago-10px">Home Purchase Value</div>
              <div style="width:500px" class="x400000-500000-Tm1siW avenir-heavy-normal-hunter-green-14px" id="home-purchase-price"><?php echo "$".number_format(toNumber($home_purchase_price_min),0,'.', ',') ?>
 - <?php echo "$".number_format(toNumber($home_purchase_price_max),0,'.', ',') ?></div>
            </div>
            <div class="frame-1251-PtFVXx">
              <div class="frame-1243-LPqasx">
                <div class="frame-1242-eumAzh">
                  <div class="use-your-rent-paymen-dC9rC7 avenir-light-hunter-green-8px">
                    Use your rent payment as a way to build toward owning your home
                  </div>
                  <div class="creates-a-path-to-ho-dC9rC7 avenir-light-hunter-green-8px">
                    Creates a path to homeownership for someone with a low credit score and/or lacking savings for a
                    down payment
                  </div>
                  <div class="best-fit-for-a-poten-dC9rC7 avenir-regular-normal-hunter-green-8px">
                    <span class="span0-xeQOnK avenir-regular-normal-hunter-green-8px">Best fit for:</span
                    ><span class="span1-xeQOnK avenir-light-hunter-green-8px"
                      >&nbsp;&nbsp;A potential buyer who may not qualify through a standard open market mortgage</span
                    >
                  </div>
                </div>
              </div>
              <div class="frame-1241-LPqasx">
                <div class="eligibility-what-you-need-to-qualify-eJriMK avenir-heavy-normal-hunter-green-8px">
                  Eligibility: what you need to qualify
                </div>
                <div class="fico-score-of-at-least-550-eJriMK avenir-light-hunter-green-8px">
                  FICO score of at least 550
                </div>
                <div class="x2500-monthly-income-eJriMK avenir-light-hunter-green-8px">$2,500 monthly income</div>
                <div class="x2-months-verifiable-income-eJriMK avenir-light-hunter-green-8px">
                  2 months verifiable income
                </div>
                <div class="x2000-in-bank-eJriMK avenir-light-hunter-green-8px">$2,000 in bank</div>
                <div class="background-check-eJriMK avenir-light-hunter-green-8px">Background check</div>
              </div>
            </div>
          </div>
          <div class="frame-1247-EfnMPh border-1px-athens-gray-2">
            <div class="frame-1227-IWXcxF">
              <div class="frame-1225-beY5xo">
                <div style="width:200px" class="down-payment-00-50-wkcpAI avenir-heavy-normal-hunter-green-8px" id="down-payment-percentage">
                  Down Payment (<?php echo  number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["cost-of-home-purchase"]["down-payment"] ["min-percentage"],0,'.', ',')?> - <?php echo  number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["cost-of-home-purchase"]["down-payment"] ["max-percentage"],0,'.', ',')?>%)
                </div>
                <div class="closing-costs-wkcpAI avenir-heavy-normal-hunter-green-8px">Closing Costs</div>
                <div class="service-fee-wkcpAI avenir-heavy-normal-hunter-green-8px">Service Fee</div>
              </div>
              <div class="frame-1226-beY5xo">
                <div style="width:200px" class="x0-25000-0bibFW avenir-light-hunter-green-8px" id="down-payment"><?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["cost-of-home-purchase"]["down-payment"] ["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["cost-of-home-purchase"]["down-payment"] ["max"],0,'.', ',')?></div>
                <div class="x3560-6450-0bibFW avenir-light-hunter-green-8px" id="closing-costs"><?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["cost-of-home-purchase"]["closing-costs"] ["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["cost-of-home-purchase"]["closing-costs"] ["max"],0,'.', ',')?></div>
                <div class="x0-0-0bibFW avenir-light-hunter-green-8px" id="service-fee"><?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["cost-of-home-purchase"]["service-fee"] ["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["cost-of-home-purchase"]["service-fee"] ["max"],0,'.', ',')?></div>
              </div>
            </div>
            <div class="frame-1246-IWXcxF">
              <div class="frame-1245-OxlKxR">
                <div class="lease-amount-zHurhC avenir-heavy-normal-hunter-green-8px">Lease Amount</div>
                <div class="x2667-4354-zHurhC avenir-light-hunter-green-8px" id="lease-amount"><?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["lease-amount"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["lease-amount"]["max"],0,'.', ',')?></div>
              </div>
              <div class="frame-1246-OxlKxR">
                <div class="premium-for-equity-OeHapx avenir-heavy-normal-hunter-green-8px">Premium for Equity</div>
                <div class="x10-10-OeHapx avenir-light-hunter-green-8px" id="premium"><?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["premium"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["premium"]["max"],0,'.', ',')?></div>
              </div>
              <div class="frame-1227-OxlKxR">
                <div class="frame-1225-YZlxct">
                  <div class="taxes-Y8xmfD avenir-heavy-normal-hunter-green-8px">Taxes</div>
                  <div class="insurance-Y8xmfD avenir-heavy-normal-hunter-green-8px">Insurance</div>
                  <div class="utilities-Y8xmfD avenir-heavy-normal-hunter-green-8px">Utilities</div>
                  <div class="maintenance-Y8xmfD avenir-heavy-normal-hunter-green-8px">Maintenance</div>
                </div>
                <div class="frame-1226-YZlxct">
                  <div class="x0-0-wcuZpn avenir-light-hunter-green-8px" id="taxes"><?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["taxes"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["taxes"]["max"],0,'.', ',')?></div>
                  <div class="x16-43-wcuZpn avenir-light-hunter-green-8px" id="insurance"><?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["insurance"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["insurance"]["max"],0,'.', ',')?></div>
                  <div class="x14-43-wcuZpn avenir-light-hunter-green-8px" id="utilities"><?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["utilities"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["utilities"]["max"],0,'.', ',')?></div>
                  <div class="x25-59-wcuZpn avenir-light-hunter-green-8px" id="maintenance"><?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["maintenance"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["maintenance"]["max"],0,'.', ',')?></div>
                </div>
              </div>
            </div>
          </div>
          <div class="frame-1224-EfnMPh">
            <div class="frame-1218-lrGrD3">
              <div style="width:300px" class="costs-of-home-purchase-7HAWQC avenir-heavy-normal-hunter-green-10px">
                Costs of Home Purchase
              </div>
              <div style="width:200px" class="x3560-31450-7HAWQC avenir-heavy-normal-hunter-green-14px" id="cost-of-home-purchase"><?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["cost-of-home-purchase"]["total-cost"] ["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["cost-of-home-purchase"]["total-cost"] ["max"],0,'.', ',')?></div>
            </div>
            <div class="frame-1219-lrGrD3">
              <div class="monthly-costs-MPBD8Z avenir-heavy-normal-hunter-green-10px">Monthly Costs</div>
              <div style="width:3000px" class="x2722-4499-MPBD8Z avenir-heavy-normal-hunter-green-14px" id="monthly-costs"><?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["total-cost"]["min"],0,'.', ',')?> - <?php echo  "$".number_format($estimates["buyer-solutions"]["homeownership-accelerator"]["monthly-costs"] ["total-cost"]["max"],0,'.', ',')?></div>
            </div>
          </div>
        </div>
        <div class="frame-1255-EMkVDE">
          <div style="width:500px">
    <span id="tid" class="badge badge-pill bg-light"></span>
          </div>
          <div class="frame-1214-zNPyhL">
            <a href="#" onclick="this.href=document.location.search+'&submit_offer_request=on';"><div id="get_offers" class="get-offers-from-zavvie-7xVlQD avenir-medium-white-14px">Get Offers from zavvie</div></a>
          </div>
        </div>
      </div>
    </div>
    <script type="text/javascript" src="assets/scripts.js"></script>
    <script type="text/javascript">
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
