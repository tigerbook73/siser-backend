<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="https://js.digitalriverws.com/v1/css/DigitalRiver.css" type="text/css" />
    <script type="text/javascript" src="https://js.digitalriverws.com/v1/DigitalRiver.js"></script>
  </head>

  <body>
    <div id="drop-in"></div>

    <script>
      const public_key = "pk_test_f17c83476a3c4197a37df49cff3875f0";
      const session_id = "2eefd3ab-4df7-42e4-bd5e-72187b7a78f2";
      let digitalriverpayments = new DigitalRiver(public_key, {
        locale: "UK",
      });

      let configuration = {
        sessionId: session_id,
        options: {
          button: {
            type: "custom",
            buttonText: "Customized Button Text",
          },
          flow: "checkout",
          // redirect: {
          //   disableAutomaticRedirects: true,
          // },
          showComplianceSection: true,
          showSavePaymentAgreement: true,
          showTermsOfSaleDisclosure: true,
          usage: "subscription",
        },
        // billingAddress: {
        //   firstName: "John",
        //   lastName: "Doe",
        //   email: "test@test.com",
        //   phoneNumber: "952-253-1234",
        //   address: {
        //     line1: "87 Nerrigundah Drive",
        //     line2: "",
        //     city: "GRANTVILLE",
        //     state: "Victoria",
        //     postalCode: "3984",
        //     country: "AU",
        //   },
        // },
        onSuccess: function (data) {
          console.log("onSuccess");
        },
        onError: function (data) {
          console.log("onError");
        },
        onReady: function (data) {
          console.log("onReady");
        },
        onCancel: function (data) {
          console.log("onCancel");
        },
      };

      let dropin = digitalriverpayments.createDropin(configuration);
      dropin.mount("drop-in");
    </script>
  </body>
</html>
