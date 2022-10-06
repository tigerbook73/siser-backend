<!DOCTYPE html>
<html>

<head>
  <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet" type="text/css">
  <link href="https://cdn.jsdelivr.net/npm/quasar@2.7.5/dist/quasar.prod.css" rel="stylesheet" type="text/css">
</head>

<body>
  <div id="q-app" class="fullscreen column justify-center">
    <div class="col-6 text-black text-center">
      @php $errorDisplayMessage = strToLower($errorMessage) @endphp
      <h4>{{ "Sorry, $errorDisplayMessage." }}</h4>
      <p>{{ "Error: $errorCode : $errorMessage" }}</p>
    </div>
  </div>

  <!-- Add the following at the end of your body tag -->
  <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/quasar@2.7.5/dist/quasar.umd.prod.js"></script>
  <script>
    const app = Vue.createApp()
    app.use(Quasar)
    app.mount('#q-app')
  </script>
</body>

</html>
