<!DOCTYPE html>
<html>
  <head>
    <title>Template Code - Transparent Payment</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
  </head>
  <body>
    <div class="col-lg-6 offset-lg-3">
        <div class="container_payment">
            <div class="payment-details">
              <form action="payment.php" method="post" id="paymentForm">
                  <h3 class="title">Buyer Details</h3>
                  <div class="row">
                    <div class="form-group col">
                      <label for="email">E-Mail</label>
                      <input id="email" name="email" type="text" class="form-control" value="tester@test.com">
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-sm-5">
                      <label for="docType">Document Type</label>
                      <select id="docType" data-checkout="docType" type="text" class="form-control"></select>
                    </div>
                    <div class="form-group col-sm-7">
                      <label for="docNumber">Document Number</label>
                      <input id="docNumber" data-checkout="docNumber" type="text" value="33333333" class="form-control"/>
                    </div>
                  </div>
                  <br>
                  <h3 class="title">Card Details</h3>
                  <div class="row">
                    <div class="form-group col-sm-8">
                      <label for="cardholderName">Card Holder</label>
                      <input id="cardholderName" data-checkout="cardholderName" type="text" class="form-control" value="tester">
                    </div>
                    <div class="form-group col-sm-4">
                      <label for="">Expiration Date</label>
                      <div class="input-group expiration-date">
                        <input type="text" class="form-control" placeholder="MM" id="cardExpirationMonth" data-checkout="cardExpirationMonth"
                          onselectstart="return false" onpaste="return false" onCopy="return false" onCut="return false" onDrag="return false" onDrop="return false" autocomplete=off value="11">
                        <span class="date-separator">/</span>
                        <input type="text" class="form-control" placeholder="YY" id="cardExpirationYear" data-checkout="cardExpirationYear"
                          onselectstart="return false" onpaste="return false" onCopy="return false" onCut="return false" onDrag="return false" onDrop="return false" autocomplete=off value="25">
                      </div>
                    </div>
                    <div class="form-group col-sm-8">
                      <label for="cardNumber">Card Number</label>
                      <input type="text" class="form-control input-background" id="cardNumber" data-checkout="cardNumber"
                        onselectstart="return false" onpaste="return false" onCopy="return false" onCut="return false" onDrag="return false" onDrop="return false" autocomplete=off value="5031755734530604">
                    </div>
                    <div class="form-group col-sm-4">
                      <label for="securityCode">CVV</label>
                      <input id="securityCode" data-checkout="securityCode" type="text" class="form-control"
                        onselectstart="return false" onpaste="return false" onCopy="return false" onCut="return false" onDrag="return false" onDrop="return false" autocomplete=off value="123">
                    </div>
                   
                    <div class="form-group col-sm-12">
                      <input type="hidden" name="transactionAmount" value="10" />
                      <input type="hidden" name="paymentMethodId" id="paymentMethodId" />
                      <input type="hidden" name="description" id="description" />
                      <br>
                      <button type="submit" class="btn btn-primary btn-block">Pay</button>
                      <br>
                     
                    </div>
                  </div>
              </form>
            </div>
          </div>
        </div>
    </div>
  </body>
</html>
<script>
window.Mercadopago.setPublishableKey("TEST-899e399b-7e2a-4193-b4c7-b8302af581b9");
window.Mercadopago.getIdentificationTypes();
  
document.getElementById('cardNumber').addEventListener('change', guessPaymentMethod);
function guessPaymentMethod(event) {
    cleanCardInfo();

    let cardnumber = document.getElementById("cardNumber").value;
    if (cardnumber.length >= 6) {
        let bin = cardnumber.substring(0,6);
        window.Mercadopago.getPaymentMethod({
            "bin": bin
        }, setPaymentMethod);
    }
};

function setPaymentMethod(status, response) {
    if (status == 200) {
        let paymentMethod = response[0];
        
        document.getElementById('paymentMethodId').value = paymentMethod.id;
        document.getElementById('cardNumber').style.backgroundImage = 'url(' + paymentMethod.thumbnail + ')';
        
        if(paymentMethod.additional_info_needed.includes("issuer_id")){
            getIssuers(paymentMethod.id);

        } else {
            
            getInstallments(
                paymentMethod.id,
                document.getElementById('amount').value
            );
        }

    } else {
        alert(`payment method info error: ${response}`);
    }
}



//Proceed with payment
doSubmit = false;
document.getElementById('paymentForm').addEventListener('submit', getCardToken);
function getCardToken(event){
    event.preventDefault();
    if(!doSubmit){
        let $form = document.getElementById('paymentForm');
        window.Mercadopago.createToken($form, setCardTokenAndPay);

        return false;
    }
};

function setCardTokenAndPay(status, response) {
    if (status == 200 || status == 201) {
        let form = document.getElementById('paymentForm');
        let card = document.createElement('input');
        card.setAttribute('name', 'token');
        card.setAttribute('type', 'hidden');
        card.setAttribute('value', response.id);
        form.appendChild(card);
        doSubmit=true;
        form.submit(); //Submit form data to your backend
    } else {
        alert("Verify filled data!\n"+JSON.stringify(response, null, 4));
    }
};

</script>