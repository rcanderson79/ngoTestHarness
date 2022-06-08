<?php

  header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
  header("Pragma: no-cache"); // HTTP 1.0.
  header("Expires: 0"); // Proxies.

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "DTD/xhtml1-strict.dtd">

<html>

	<head>

		<title>N-Genius Online Test Harness</title>

		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>		
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>		

    <link rel="stylesheet" type="text/css" href="include/lightcase/src/css/lightcase.css">
    <script type="text/javascript" src="include/lightcase/src/js/lightcase.js"></script>    
    
		<script>
				
			function CopyToClipboard(origin) 
			{ 
				var controlValue = $(origin).val();
				origin.select();
				document.execCommand('copy');
			}
      
      function openLightbox() {
        
        lightcase.start({
          href: $("#order_paymentpage").val(),
        });
        
      }
								
			$(document).ready(function(){

        $('a[data-rel^=lightcase]').lightcase();
        
				$.get("config.json", function(data) { $("#config").val(data); setConfiguration(); },'text');
        $('#loadEnvironments').on('click', function() { $('#file-input').trigger('click'); });
				$("#environment").change( function() { 
				
					var chosen = parseInt($(this).val());
					var config = JSON.parse($("#config").val());
					if (chosen == -1) {
					
						$("#keycloakUrl").val("");
						$("#txnServiceUrl").val("");
						$("#apikey").val("");
						$("#outlet_id").val("");
						
					} else {
						
            var realm = config.environments[chosen].realm;
						var idserv = config.environments[chosen].endpoints.identity + "/auth/realms/" + realm + "/protocol/openid-connect/token";
						var txnserv = config.environments[chosen].endpoints.transaction;
						var outkey = config.environments[chosen].outlets[0].apikey;
						var outref = config.environments[chosen].outlets[0].reference;
						
						$("#keycloakUrl").val(idserv);
						$("#txnServiceUrl").val(txnserv);
						$("#apikey").val(outkey);
						$("#outlet_id").val(outref);
						
					}
				
				});
				
				$("#order_create :input").prop("disabled", true);					
				$("#order_manage :input").prop("disabled", true);					

				$("#panel_1").css("background-color", "#ffffff");
				$("#panel_2").css("background-color", "#ffffff");
				$("#panel_2").css("background-color", "#ffffff");
				
				setPanel(1);
				
				$("#get_bearer_token").click( function() { getBearerToken(); } );
				$("#clear_bearer_token").click( function() { $("#bearer_token").val(""); $("#bearer_token").change(); } );
				$("#create_order").click( function() { createOrder(); } );
				$("#get_order").click( function() { getOrder(); } );
				$("#send_card_payment").click( function() { sendCardData(); } );
        $("#get_pares_md").click( function() { get_iframe_3ds_data(); } );

				$("#bearer_token").change( function() { 
				
					if ( $(this).val() != null && $(this).val() != '') 
					{ 
					
						$("#clear_bearer_token").attr('disabled', false); 
						$("#order_create :input").prop("disabled", false);					
						$("#get_order").attr('disabled', false); 
						setPanel(2);
						
					} 
					else 
					{ 
					
						$("#clear_bearer_token").attr('disabled', true); 
						$("#order_create :input").prop("disabled", true);					
						$("#get_order").attr('disabled', true); 
						
					}
				
				} );
				
				$("#order_reference").change( function() {
				
					if ( $(this).val() != null && $(this).val() != '' )
					{
					
						$("#order_manage :input").prop("disabled", false);											
					
					}
					else
					{
					
						$("#order_manage :input").prop("disabled", true);					

					}
				
				} );
		
				function setConfiguration() {
					
					var config = JSON.parse($("#config").val());
					console.log("Configuration set with config.json: ", config);
					
					for(var env in config.environments) {
						
						var envName = config.environments[env].name;
						var envDesc = config.environments[env].description;
						
						var opt = new Option(envName + " | " + envDesc, env);
						$(opt).html(envName + " | " + envDesc);
						$("#environment").append(opt);
												
					}
					
					$("#environment").val("0").change();
					
				}
        
        function loadFromFile() {
          
        }
				
				function setPanel(num) {
				
					$("#panel_1").css("background-color", "#ffffff");
					$("#panel_2").css("background-color", "#ffffff");
					$("#panel_2").css("background-color", "#ffffff");
					
					if (num == 1) { $("#panel_1").css("background-color", "#eeffee"); }
					if (num == 2) { $("#panel_2").css("background-color", "#eeffee"); }
					if (num == 3) { $("#panel_3").css("background-color", "#eeffee"); }
				
				}
				
				function handleTokenError(errdata) {
					
					$("#token_error").toggle(true);
					$("#token_error_http").html("Error <b>" + errdata.httpcode + "</b> (" + errdata.response.error + ")");
					$("#token_error_message").html(errdata.response.error_description);
					
				}
				
				function handleCreateError(errdata) {
					
					$("#create_error").toggle(true);
					$("#create_error_http").html("Error <b>" + errdata.httpcode + "</b> (" + errdata.response.message + ")");
					$("#create_error_message").html(errdata.response.errors[0].message);
					
				}
				
				function getBearerToken() {
				
					$("#token_error").toggle(false);
					$("#get_bearer_token").attr('disabled', true);
				
					var endpoint = $("#keycloakUrl").val();
					var apikey = $("#apikey").val();
					
					$.post('token.php', { action: 'gettoken', 'endpoint': endpoint, 'apikey': apikey },
					function(data, status){
						
						var message = JSON.parse(data);
						console.log("Token request response: ", message);

						var code = message.httpcode;						
						var response = message.response;						
						
						if (code == 200) {
							
							$("#bearer_token").val(response.access_token);
							$("#bearer_token").change();
							
							
						} else {
						
							handleTokenError(message);
						
						}
						
						$("#get_bearer_token").attr('disabled', false);

					});
				
				}
				
				function createOrder() {

					$("#create_error").toggle(false);
					$("#order_create :input").prop("disabled", true);					
				
					var pToken = $("#bearer_token").val();
					var pOutlet = $("#outlet_id").val();
					var pEndpoint = $("#txnServiceUrl").val() + "/transactions/outlets/" + pOutlet + "/orders";
					
					var ord_act = $("#order_action").val();
					var ord_lang = $("#order_language").val();
					var ord_curr = $("#order_currency").val();
					var ord_amount = $("#order_amount").val();
					var ord_desc = $("#order_description").val();
					var ord_redirect = $("#order_redirect").val();
					var ord_email = $("#order_email").val();
					var ord_merchantref = $("#order_merchantref").val();
				
					var sendData = {
					
						action: ord_act,
						amount: { currencyCode: ord_curr, value: parseInt(ord_amount) },
						language: ord_lang,
						description: ord_desc,
						merchantAttributes: { redirectUrl: ord_redirect, cancelUrl:"https://pullingteeth.co.uk/network" },
            emailAddress: ord_email
            //merchantOrderReference: ord_merchantref
					
					};
					var pPayload = JSON.stringify(sendData);
					
					$.post('token.php', { action: 'createorder', 'endpoint': pEndpoint, 'token': pToken, 'payload': pPayload },
					function(data, status) {

						$("#order_create :input").prop("disabled", false);					
						parseOrderResponse(data);
						
						$("#order_reference").change();
						setPanel(3);						
						
					});
										
				}
        
        function sendCardData() {

          $("#send_card_payment").prop('disabled', true);
          
					var putPAN = $("#card_pan").val();
					var putExpiry = $("#card_expires_year").val() + "-" + $("#card_expires_month").val();
          var putCVV = $("#card_security_code").val();
          var putName = $("#card_name").val();
          
 					var pToken = $("#bearer_token").val();
          var pEndpoint = $("#card_payment_url").val();
					var sendData = {
					
						pan: putPAN,
						expiry: putExpiry,
						cvv: putCVV,
            cardholderName: putName
            
					};
          var pPayload = JSON.stringify(sendData);
          
					$.post('token.php', { action: 'sendcard', 'endpoint': pEndpoint, 'token': pToken, 'payload': pPayload },
					function(data, status) {
					
						parsePaymentResponse(data);
						$("#get_order").attr('disabled', false); 
						
					});
          
        }
				
				function getOrder() {

					var pToken = $("#bearer_token").val();
					var pOutlet = $("#outlet_id").val();
					var pOrder = $("#order_reference").val();
					var pEndpoint = $("#txnServiceUrl").val() + "/transactions/outlets/" + pOutlet + "/orders/" + pOrder;

					$("#get_order").attr('disabled', true); 
					
					$.post('token.php', { action: 'getorder', 'endpoint': pEndpoint, 'token': pToken },
					function(data, status) {
					
						parseOrderResponse(data);
						$("#get_order").attr('disabled', false); 
						
					});
				
				}
        
        function parsePaymentResponse(data) {
          
          var message = JSON.parse(data);
          console.log("Payment request (PUT) response: ", message)
          var code = message.httpcode;
          var response = message.response;
          					
					if (code == 201 || code == 200) {

            var order_reference = response.orderReference;
            var order_state = response.state;
						var payment_ref = response._id.replace("urn:payment:", "");
            
            $("#order_reference").val(order_reference);
						$("#order_state").val(order_state);
						$("#payment_reference").val(payment_ref);

            if (order_state == "AWAIT_3DS") {

              var pEndpoint = encodeURI(response['3ds'].acsUrl);
              var tUrl = encodeURI(response._links['cnp:3ds'].href);
              var preq = response['3ds'].acsPaReq;
              var mdin = response['3ds'].acsMd;
              var src = "handle_3ds.php?acsUrl="+encodeURI(pEndpoint)+"&TermUrl="+encodeURI(tUrl)+"&PaReq="+encodeURIComponent(preq)+"&MD="+mdin;
              
              $("#3ds_iframe").attr('src', src);

            }
                        
          }
          
        }
	
				function parseOrderResponse(data) {
				
					var message = JSON.parse(data);
          console.log(message);
					console.log("Order request (GET/POST) response: ", message);

					var code = message.httpcode;						
					var response = message.response;						
					
					if (code == 201 || code == 200) {

						var paypage_link = "";
						var links = response._links;
						if (links.hasOwnProperty("payment")) {
							paypage_link = response._links.payment.href;
							$("#go_to_paymentpage").click( function() { window.open(paypage_link); } );
							//$("#lightbox_paymentpage").attr("href", paypage_link);
							//$("#iframe_paymentpage").attr("src", paypage_link);
							$("#go_to_paymentpage").prop("disabled", false);
						}
						var order_ref = response.reference;
            
            var links = response._embedded.payment[0]._links;            
						var payment_ref = response._embedded.payment[0]._id.replace("urn:payment:", "");
            if (links.hasOwnProperty("payment:card")) { var payment_url = response._embedded.payment[0]._links['payment:card'].href; } else { payment_url = "NOT FOUND"; }
						var order_state = response._embedded.payment[0].state;

						$("#order_paymentpage").val(paypage_link);
						$("#order_reference").val(order_ref);
						$("#order_state").val(order_state);
						$("#payment_reference").val(payment_ref);										
            $("#card_payment_url").val(payment_url);
            						
					} else {
					
						handleCreateError(message);
					
					}			
					
				}
        
        function get_iframe_3ds_data() {
          
          var pr3ds = $("#3ds_iframe").contents().find("#PaRes").val();
          var md3ds = $("#3ds_iframe").contents().find("#MDRes").val();
          
          $("#3ds_pares").val(pr3ds);
          $("#3ds_md").val(md3ds);
          
        }
				
			});

		</script>

	</head>

	<body>
	
		<div class="container">
					
			<textarea id="config" style="width:100%; display:none;">
			</textarea>
		
			<div class="row">
			
				<div class="col-sm" id="panel_1" style="padding-bottom:25px;">
				
					<h3 style="text-align:center; margin-top:20px;">1. Identify Yourself</h3>
					<p style="text-align:center; margin-bottom:20px;">Select your environment and generate/input an access token to get started</p>
					
					<hr>

					<div class="form-group">
            <label for="environment">Environment:</label> 
            <span class="input-group-btn">
              <button class="btn btn-info btn-sm" style="float:right; max-height:25px; display:none;" id="loadEnvironments">...</button>
              <select class="form-control" id="environment">
                <option value=-1 selected>Custom</option>
              </select>
            </span>
					</div>
          
          <input id="file-input" type="file" name="name" style="display: none;" /> <!-- hidden file dialog -->

					<div class="form-group">				
						<label for="keycloakUrl">Identity Service:</label><small style="color:gray; float:right; cursor:pointer;" onclick="CopyToClipboard(document.getElementById('keycloakUrl'));">[COPY]</small>
						<input type="text" class="form-control" id="keycloakUrl" placeholder="Enter identity service location" value="">
					</div>
					
					<div class="form-group">				
						<label for="txnServiceUrl">Transaction Service:</label><small style="color:gray; float:right; cursor:pointer;" onclick="CopyToClipboard(document.getElementById('txnServiceUrl'));">[COPY]</small>
						<input type="text" class="form-control" id="txnServiceUrl" placeholder="Enter transaction service location" value="">
					</div>

					<hr>

					<div class="form-group">				
						<label for="apikey">API Key:</label><small style="color:gray; float:right; cursor:pointer;" onclick="CopyToClipboard(document.getElementById('apikey'));">[COPY]</small>
						<input type="text" class="form-control" id="apikey" placeholder="Enter a valid API key" value="">
					</div>
					
					<hr>
					
					<button type="button" class="btn btn-primary btn-block" id="get_bearer_token">Get Access Token</button>

					<hr>
					
					<div id="token_error" style="display:none; color:red;">
						<center>
							<span id="token_error_http"><b>400</b> (Bad Request)</span>
							<br>
							<span id="token_error_message">Invalid client credentials</span>
						</center>
					</div>
          
          <iframe id="3ds_iframe" src="handle_3ds.php" width=100% height=400>
            
          </iframe>
					
				</div>

				<div class="col-sm" id="panel_2" style="padding-bottom:25px;">

					<h3 style="text-align:center; margin-top:20px;">2. Create Order</h3>
					<p style="text-align:center; margin-bottom:20px;">You'll need an access token and a valid outlet reference for this step.</p>
					
					<hr>

					<div class="form-group">				
						<label for="bearer_token">Access Token:</label><small style="color:gray; float:right; cursor:pointer;" onclick="CopyToClipboard(document.getElementById('bearer_token'));">[COPY]</small>
						<input type="text" class="form-control" id="bearer_token" placeholder="[empty]" value="">
					</div>

					<button type="button" class="btn btn-secondary btn-block" id="clear_bearer_token" disabled>Clear Token</button>
					
					<hr>
					
					<form id="order_create">

						<div class="form-group">				
							<label for="outlet_id">Outlet Reference:</label><small style="color:gray; float:right; cursor:pointer;" onclick="CopyToClipboard(document.getElementById('outlet_id'));">[COPY]</small>
							<input type="text" class="form-control" id="outlet_id" placeholder="Enter a valid outlet reference" value="">
						</div>						
						
						<hr>

						<div class="form-group">
							<label for="order_action">Payment action:</label>
							<select class="form-control" id="order_action">
								<option value="AUTH" selected>Auth only (AUTH)</option>
								<option value="SALE">Sale (SALE)</option>
								<option value="PURCHASE">Purchase (PURCHASE)</option>
								<option value="VERIFY">Verify (VERIFY)</option>
							</select>
						</div>
						
						<div class="form-group">
							<label for="order_language">Language:</label>
							<select class="form-control" id="order_language">
								<option value="en" selected>English (en)</option>
								<option value="ar">Arabic (ar)</option>
								<option value="fr">French (fr)</option>
							</select>
						</div>

						<div class="form-row">
						
							<div class="form-group col-sm-6">
								<label for="order_currency">Currency:</label>
								<select class="form-control" id="order_currency">
									<option value="AED" selected>AED</option>
									<option value="USD">USD</option>
									<option value="GBP">GBP</option>
									<option value="EUR">EUR</option>
									<option value="SAR">SAR</option>
									<option value="NAD">NAD</option>
									<option value="UGX">UGX</option>
									<option value="SEK">SEK</option>
									<option value="DKK">DKK</option>
									<option value="PHP">PHP</option>
									<option value="ZAR">ZAR</option>
									<option value="HKD">HKD</option>
									<option value="TZS">TZS</option>
									<option value="MWK">MWK</option>
									<option value="OMR">OMR</option>
									<option value="EGP">EGP</option>
									<option value="GHS">GHS</option>
									<option value="XOF">XOF</option>
									<option value="KES">KES</option>
									<option value="BWP">BWP</option>
									<option value="AUD">AUD</option>
									<option value="CAD">CAD</option>
									<option value="LKR">LKR</option>
									<option value="MAD">MAD</option>
									<option value="MYR">MYR</option>
									<option value="PKR">PKR</option>
									<option value="THB">THB</option>
									<option value="TRY">TRY</option>
									<option value="XAF">XAF</option>
									<option value="GNF">GNF</option>
								</select>
							</div>
								
							<div class="form-group col-sm-6">				
								<label for="order_amount">Amount <small>(minor units *)</small>:</label>
								<input type="number" class="form-control" id="order_amount" placeholder="[amount.value]" value="100">
								<small style="float:right;">* i.e. 1000 = 10.00 AED</small>
							</div>
								
						</div>

						<div class="form-group">				
							<label for="order_email">Email Address:</label>
							<input type="text" class="form-control" id="order_email" placeholder="[email]" value="">
						</div>            

						<div class="form-group">				
							<label for="order_description">Description:</label>
							<input type="text" class="form-control" id="order_description" placeholder="[description]" value="1 x Big Widget">
						</div>

            <!--
            <div class="form-group">				
							<label for="order_merchantref">Merchant Ref:</label>
							<input type="text" class="form-control" id="order_merchantref" placeholder="[merchantRef]" value="robintest123">
						</div>
            -->
						
						<div class="form-group">				
							<label for="order_redirect">Redirect URL:</label><small style="color:gray; float:right; cursor:pointer;" onclick="CopyToClipboard(document.getElementById('order_redirect'));">[COPY]</small>
							<input type="text" class="form-control" id="order_redirect" placeholder="[merchantAttributes.redirectUrl]" value="https://pullingteeth.co.uk/network/">
						</div>
						
						<hr>

						<button type="button" class="btn btn-primary btn-block" id="create_order">Create New Order</button>

					</form>

					<div id="create_error" style="display:none; color:red;">
						<center>
							<span id="create_error_http"><b>400</b> (Bad Request)</span>
							<br>
							<span id="create_error_message">Invalid client credentials</span>
						</center>
					</div>
					
				</div>

				<div class="col-sm" id="panel_3" style="padding-bottom:25px;">

					<h3 style="text-align:center; margin-top:20px;">3. Complete Payment</h3>
					<p style="text-align:center; margin-bottom:20px;">To access these functions, you'll need an access token and a valid order reference.</p>
					
					<hr>

					<div class="form-group">				
						<label for="order_paymentpage">Payment Page:</label><small style="color:gray; float:right; cursor:pointer;" onclick="CopyToClipboard(document.getElementById('order_paymentpage'));">[COPY]</small>
						<input type="text" class="form-control" id="order_paymentpage" placeholder="[empty]" value="">
					</div>			
					
					<form id="order_manage">

						<div class="form-group">				
							<label for="order_reference">Order Reference:</label><small style="color:gray; float:right; cursor:pointer;" onclick="CopyToClipboard(document.getElementById('order_reference'));">[COPY]</small>
							<input type="text" class="form-control" id="order_reference" placeholder="[empty]" value="">
						</div>

						<button type="button" class="btn btn-success btn-block" id="go_to_paymentpage">Visit Payment Page</button>

            <!--
            <center>
              <a href="" id="lightbox_paymentpage" class="btn" onclick="openLightbox();">Open in Lightbox</a> 
            </center>

              <hr>
              <iframe width=100% height=550px id="iframe_paymentpage">
              </iframe>
            -->            

						<hr>

						<button type="button" class="btn btn-default btn-block" id="get_order">Get Order Status</button>

						<hr>
						
						<div class="form-group">				
							<label for="order_state">Order State:</label>
							<input type="text" class="form-control" id="order_state" placeholder="[empty]" value="">
						</div>

						<div class="form-group">				
							<label for="payment_reference">Payment Reference:</label><small style="color:gray; float:right; cursor:pointer;" onclick="CopyToClipboard(document.getElementById('payment_reference'));">[COPY]</small>
							<input type="text" class="form-control" id="payment_reference" placeholder="[empty]" value="">
						</div>
            
            <hr>
                        
            <div class="form-group">				
							<label for="card_name">Cardholder Name:</label>
							<input type="text" class="form-control" id="card_name" placeholder="[name]" value="Mr Test">
						</div>            
            
						<div class="form-group">				
							<label for="card_pan">Card number / PAN:</label>
							<input type="text" class="form-control" id="card_pan" placeholder="" value="" maxlength="18">
						</div>
            
						<div class="form-row">
						
              <div class="form-group col-sm-4">
                <label for="card_expires_month">Expiry (MM):</label>
								<select class="form-control" id="card_expires_month">
									<option value="01">01</option>
									<option value="02">02</option>
									<option value="03">03</option>
									<option value="04">04</option>
									<option value="05">05</option>
									<option value="06" selected>06</option>
									<option value="07">07</option>
									<option value="08">08</option>
									<option value="09">09</option>
									<option value="10">10</option>
									<option value="11">11</option>
									<option value="12">12</option>
								</select>
              </div>

              <div class="form-group col-sm-4">				
                <label for="card_expires_year">Expiry (YY):</label>
								<select class="form-control" id="card_expires_year">
									<option value="2019" selected>2019</option>
									<option value="2020">2020</option>
									<option value="2021">2021</option>
									<option value="2022">2022</option>
									<option value="2023">2023</option>
									<option value="2024">2024</option>
									<option value="2025">2025</option>
								</select>
              </div>

              <div class="form-group col-sm-4">				
                <label for="card_security_code">CSC/CVV:</label>
                <input type="number" class="form-control" id="card_security_code" placeholder="" value="123" max=9999>
              </div>

            </div>
            
            <div class="form-group" hidden>				
              <label for="card_security_code">Card Payment URL:</label>
              <input type="text" class="form-control" id="card_payment_url" placeholder="[empty]" value="">
            </div>
            
            <hr>

						<button type="button" class="btn btn-primary btn-block" id="send_card_payment">Send Card Payment</button>
            
            <hr>
            
						<div class="form-row">

              <div class="form-group col-sm-6">				
                <button type="button" class="btn btn-default btn-block" id="get_pares_md">Get PaRes/MD</button>
              </div>
              <div class="form-group col-sm-6">				
                <button type="button" class="btn btn-primary btn-block" id="send_pares_md">Send PaRes/MD</button>
              </div>
              
            </div>

            <hr>

						<div class="form-row">
						
              <div class="form-group col-sm-6">				
                <label for="3ds_pares">PaRes:</label>
                <input type="text" class="form-control" id="3ds_pares" placeholder="[empty]" value="">
              </div>

              <div class="form-group col-sm-6">				
                <label for="3ds_md">MD:</label>
                <input type="text" class="form-control" id="3ds_md" placeholder="[empty]" value="">
              </div>
            
					</form>
					
				</div>

			</div>

		</div>
		
	</body>
	
</html>