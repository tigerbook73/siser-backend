@props(['invitation', 'helper'])

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Document</title>

    <style>
      .mail-content {
        line-height: 1.3em;
        font-size: 1.1em;
        margin: auto;
        max-width: 800px;
      }

      table,
      table th {
        padding: 2px 5px 2px 5px;
        border-top: 1px solid gray;
        border-bottom: 1px solid gray;
        border-collapse: collapse;
      }

      table {
        min-width: 600px;
        max-width: 800px;
        width: 100%;
        margin-top: 5px;
        border-top: 2px solid lightgray;
        border-bottom: 2px solid lightgray;
      }

      table tr td:first-child {
        width: 40%;
      }

      .highlight {
        background-color: lightgray;
        color: black;
        font-weight: bold;
      }

      .text-left {
        text-align: left;
      }

      .text-right {
        text-align: right;
      }

      table th {
        /* background-color: #1976d2; */
        /* color: white; */
        /* font-weight: bold; */
        text-align: right;
        /* min-width: 200px; */
      }
    </style>
  </head>

  <body>
    <div class="mail-content">
      <!-- greeting -->
      <br />
      {{ $helper->trans('layout.greeting', ['name' => $helper->formatName([
        'first_name' => $invitation->guest->given_name,
        'last_name'  => $invitation->guest->family_name,
      ])]) }}
      <br />
      <br />

      <!-- main content -->
      <div>
        {{ $slot }}
      </div>
      <br />

      <!-- faq -->
      {!! $helper->trans('layout.faqs', ['support_link' => $helper->getSupportLink()]); !!}
      <br />
      <br />

      <!-- faq -->

      {!!
        $helper->trans('layout.contact_us', [
          'support_email_link' => $helper->getSupportEmailLink(),
          'customer_support_link' => $helper->getCustomerSupportLink(),
        ]);
      !!}
      <br />
      <br />

      <!-- thank you -->
      {{ $helper->trans('layout.regards') }}
      <br />
      <div>Team Siser</div>
      <br />
      <br />

      <!-- logo -->
      <div>
        <img width="110" height="46" src="{{ config('app.url') . '/static/imgs/siser-logo-trimmed.png'}}" />
      </div>
      <br />
    </div>
  </body>
</html>
