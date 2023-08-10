@props([ 'country' => 'JP'])

@php
  $helper = new class 
  {
    public $country = 'default';

    public function __construct(
      public $entityDrInc = [],
      public $entityDrIreland = [],
      public $entityDrUk = [],
      public $countryMappings = [],
    ) {
      $this->entityDrInc = [
        'name' => "Digital River Inc.",
        'url' => "https://store.digitalriver.com/store/defaults/{{locale}}/DisplayDRAboutDigitalRiverPage/eCommerceProvider.Digital%20River%20Inc.",
      ];
      $this->entityDrIreland = [
        'name' => "Digital River Ireland Ltd.",
        'url' => "https://store.digitalriver.com/store/defaults/{{locale}}/DisplayDRAboutDigitalRiverPage/eCommerceProvider.Digital%20River%20Ireland%20Ltd.",
      ];
      $this->entityDrUk = [
        'name' => "Digital River UK Ltd.",
        'url' => "https://store.digitalriver.com/store/defaults/{{locale}}/DisplayDRAboutDigitalRiverPage/eCommerceProvider.Digital%20River%20UK%20Ltd.",
      ];
      $this->countryMappings = [
        'US'      => ['entity' => $this->entityDrInc,     'locale' => "en_US",],
        'AU'      => ['entity' => $this->entityDrIreland, 'locale' => "en_AU",],
        'CA'      => ['entity' => $this->entityDrIreland, 'locale' => "en_CA",],
        'DE'      => ['entity' => $this->entityDrIreland, 'locale' => "de_DE",],
        'ES'      => ['entity' => $this->entityDrIreland, 'locale' => "es_ES",],
        'FR'      => ['entity' => $this->entityDrIreland, 'locale' => "fr_FR",],
        'GB'      => ['entity' => $this->entityDrUk,      'locale' => "en_GB",],
        'IT'      => ['entity' => $this->entityDrIreland, 'locale' => "it_IT",],
        'JP'      => ['entity' => $this->entityDrIreland, 'locale' => "en_IE",],
        'NZ'      => ['entity' => $this->entityDrIreland, 'locale' => "en_NZ",],
        'default' => ['entity' => $this->entityDrIreland, 'locale' => "en_IE",],
      ];
    }

    function setCountry(string $country)
    {
      $this->country = $country;
      return "";
    }

    function getEntity()
    {
      return $this->countryMappings[$this->country]['entity'] ?? $this->countryMappings['default']['entity'];
    }

    function getEntityUrl()
    {
      return str_replace('{{locale}}', $this->getLocale(), $this->getEntity($this->country)['url']);
    }

    function getEntityName()
    {
      return $this->getEntity($this->country)['name'];
    }

    function getLocale()
    {
      return $this->countryMappings[$this->country]['locale'] ?? $this->countryMappings['default']['locale'];
    }
  }
@endphp

<div>{{ $helper->setCountry($country) }}</div>
<div>
  <a href="{{ $helper->getEntityUrl() }}" target="_blank">{{ $helper->getEntityName() }}</a> is the authorized reseller of <b>Leonardo™ Software</b> in this online store. <br/>
  <a href="https://store.digitalriver.com/store/defaults/{{ $helper->getLocale() }}/DisplayDRPrivacyPolicyPage/eCommerceProvider.{{ $helper->getEntityName() }}." target="_blank">Privacy Policy</a> |
  <a href="https://store.digitalriver.com/store/defaults/{{ $helper->getLocale() }}/DisplayDRTermsAndConditionsPage/eCommerceProvider.{{ $helper->getEntityName() }}." target="_blank">Terms of Sale</a> |
  <a href="https://store.digitalriver.com/store/defaults/{{ $helper->getLocale() }}/DisplayDRCookiesPolicyPage/eCommerceProvider.{{ $helper->getEntityName() }}." target="_blank">Cookies</a> |
  <a href="https://store.digitalriver.com/store/defaults/{{ $helper->getLocale() }}/DisplayDRTermsAndConditionsPage/eCommerceProvider.{{ $helper->getEntityName() }}.#cancellationRight" target="_blank">Cancellation Right</a> |
  <a href="https://store.digitalriver.com/store/defaults/{{ $helper->getLocale() }}/DisplayDRContactInformationPage/eCommerceProvider.{{ $helper->getEntityName() }}." target="_blank">Legal Notice</a> |
  @if ($country == 'US')
  <a href="https://store.digitalriver.com/store/defaults/en_US/DisplayCCPAPage/eCommerceProvider.Digital River Inc." target="_blank">Your California Privacy Rights</a> |
  @endif
  @if ($country == 'JP') 
  <a href="https://software.siser.com/legislation/legislation-japan" target="_blank">特定商取引に関する法律に基づく表示</a> |
  @endif
  @if ($country == 'IT')
  <a href="https://store.digitalriver.com/store/defaults/it_IT/DisplayDRTermsAndConditionsPage/eCommerceProvider.Digital%20River%20Ireland%20Ltd.#warrantyInformation" target="_blank">Informazioni sulla Garanzia</a> |
  @endif
</div>
