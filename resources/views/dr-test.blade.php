<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="https://js.digitalriverws.com/v1/css/DigitalRiver.css" type="text/css" />
    <script type="text/javascript" src="https://js.digitalriverws.com/v1/DigitalRiver.js"></script>
  </head>

  <body>
    <h1>checkout or management</h1>
    <div id="buttons">
      <button onclick="checkout()">Checkout</button>
      <button onclick="management()">Management</button>
    </div>

    <div style="display: flex">
      <div id="drop-in-checkout"></div>
      <div id="drop-in-management"></div>
    </div>

    <script>
      const public_key = "pk_test_f17c83476a3c4197a37df49cff3875f0";
      const session_id = "2eefd3ab-4df7-42e4-bd5e-72187b7a78f2";
      const digitalRirver = new DigitalRiver(public_key, {
        locale: "UK",
      });

      // checkout configuration
      const configurationCheckout = {
        sessionId: session_id,
        options: {
          flow: "checkout",
          usage: "subscription",

          showComplianceSection: true,
          showSavePaymentAgreement: true,
          showTermsOfSaleDisclosure: true,
          expandFirstPaymentMethod: false,
        },
        onSuccess: (data) => console.log("onSuccess", data),
        onError: (data) => console.log("onError", data),
        onReady: (data) => console.log("onReady", data),
        onCancel: (data) => console.log("onCancel", data),
      };

      // management configuration
      const configurationManagement = {
        options: {
          flow: "managePaymentMethods",
          usage: "subscription",

          showComplianceSection: true,
          showSavePaymentAgreement: true,
          showTermsOfSaleDisclosure: true,
          expandFirstPaymentMethod: false,
        },
        billingAddress: {
          firstName: "John",
          lastName: "Doe",
          email: "test@test.com",
          phoneNumber: "952-253-1234",
          address: {
            line1: "87 Nerrigundah Drive",
            line2: "",
            city: "GRANTVILLE",
            state: "Vic",
            postalCode: "3984",
            country: "AU",
          },
        },
        onSuccess: (data) => console.log("onSuccess", data),
        onError: (data) => console.log("onError", data),
        onReady: (data) => console.log("onReady", data),
        onCancel: (data) => console.log("onCancel", data),
      };

      function hideButtons() {
        const el = document.getElementById("buttons");
        el.style.display = "none";
      }

      function checkout() {
        hideButtons();

        // checkout
        const dropin = digitalRirver.createDropin(configurationCheckout);
        dropin.mount("drop-in-checkout");
      }

      function management() {
        hideButtons();

        // management
        const dropin = digitalRirver.createDropin(configurationManagement);
        dropin.mount("drop-in-management");
      }
    </script>
  </body>
</html>
