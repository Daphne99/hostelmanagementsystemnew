<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models2\Main;
use App\Models2\City;
use App\Models2\State;
use App\Models2\Country;
use App\Models2\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class administrativeController extends Controller
{
    var $data = array();
	var $panelInit ;
    var $layout = 'dashboard';
    var $currencies = [
        "AED"=> [ "code"=> "AED", "name"=> "UAE Dirham", "fractionSize"=> 2, "symbol"=> ".د.إ" ],
        "AFN"=> [ "code"=> "AFN", "name"=> "Afghani", "fractionSize"=> 2, "symbol"=> "؋" ],
        "ALL"=> [ "code"=> "ALL", "name"=> "Lek", "fractionSize"=> 2, "symbol"=> "L" ],
        "AMD"=> [ "code"=> "AMD", "name"=> "Armenian Dram", "fractionSize"=> 2, "symbol"=> "դր." ],
        "ANG"=> [ "code"=> "ANG", "name"=> "Netherlands Antillean Guilder", "fractionSize"=> 2, "symbol"=> "ƒ" ],
        "AOA"=> [ "code"=> "AOA", "name"=> "Kwanza", "fractionSize"=> 2, "symbol"=> "Kz" ],
        "ARS"=> [ "code"=> "ARS", "name"=> "Argentine Peso", "fractionSize"=> 2, "symbol"=> "$" ],
        "AUD"=> [ "code"=> "AUD", "name"=> "Australian Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "AWG"=> [ "code"=> "AWG", "name"=> "Aruban Florin", "fractionSize"=> 2, "symbol"=> "ƒ" ],
        "AZN"=> [ "code"=> "AZN", "name"=> "Azerbaijanian Manat", "fractionSize"=> 2, "symbol"=> "₼" ],
        "BAM"=> [ "code"=> "BAM", "name"=> "Convertible Mark", "fractionSize"=> 2, "symbol"=> "KM" ],
        "BBD"=> [ "code"=> "BBD", "name"=> "Barbados Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "BDT"=> [ "code"=> "BDT", "name"=> "Taka", "fractionSize"=> 2, "symbol"=> "৳" ],
        "BGN"=> [ "code"=> "BGN", "name"=> "Bulgarian Lev", "fractionSize"=> 2, "symbol"=> "лв" ],
        "BHD"=> [ "code"=> "BHD", "name"=> "Bahraini Dinar", "fractionSize"=> 3, "symbol"=> ".د.ب" ],
        "BIF"=> [ "code"=> "BIF", "name"=> "Burundi Franc", "fractionSize"=> 0, "symbol"=> "FBu" ],
        "BMD"=> [ "code"=> "BMD", "name"=> "Bermudian Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "BND"=> [ "code"=> "BND", "name"=> "Brunei Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "BOB"=> [ "code"=> "BOB", "name"=> "Boliviano", "fractionSize"=> 2, "symbol"=> "Bs." ],
        "BOV"=> [ "code"=> "BOV", "name"=> "Mvdol", "fractionSize"=> 2, "symbol"=> "Bov" ],
        "BRL"=> [ "code"=> "BRL", "name"=> "Brazilian Real", "fractionSize"=> 2, "symbol"=> "R$" ],
        "BSD"=> [ "code"=> "BSD", "name"=> "Bahamian Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "BTN"=> [ "code"=> "BTN", "name"=> "Ngultrum", "fractionSize"=> 2, "symbol"=> "Nu." ],
        "BWP"=> [ "code"=> "BWP", "name"=> "Pula", "fractionSize"=> 2, "symbol"=> "P" ],
        "BYN"=> [ "code"=> "BYN", "name"=> "Belarussian Ruble", "fractionSize"=> 2, "symbol"=> "p." ],
        "BYR"=> [ "code"=> "BYR", "name"=> "Belarussian Ruble", "fractionSize"=> 0, "symbol"=> "p." ],
        "BZD"=> [ "code"=> "BZD", "name"=> "Belize Dollar", "fractionSize"=> 2, "symbol"=> "BZ$" ],
        "CAD"=> [ "code"=> "CAD", "name"=> "Canadian Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "CDF"=> [ "code"=> "CDF", "name"=> "Congolese Franc", "fractionSize"=> 2, "symbol"=> "FC" ],
        "CHE"=> [ "code"=> "CHE", "name"=> "WIR Euro", "fractionSize"=> 2, "symbol"=> "CHE" ],
        "CHF"=> [ "code"=> "CHF", "name"=> "Swiss Franc", "fractionSize"=> 2, "symbol"=> "fr." ],
        "CHW"=> [ "code"=> "CHW", "name"=> "WIR Franc", "fractionSize"=> 2, "symbol"=> "CHW" ],
        "CLF"=> [ "code"=> "CLF", "name"=> "Unidad de Fomento", "fractionSize"=> 4, "symbol"=> "UF" ],
        "CLP"=> [ "code"=> "CLP", "name"=> "Chilean Peso", "fractionSize"=> 0, "symbol"=> "$" ],
        "CNY"=> [ "code"=> "CNY", "name"=> "Yuan Renminbi", "fractionSize"=> 2, "symbol"=> "元" ],
        "COP"=> [ "code"=> "COP", "name"=> "Colombian Peso", "fractionSize"=> 0, "symbol"=> "$" ],
        "COU"=> [ "code"=> "COU", "name"=> "Unidad de Valor Real", "fractionSize"=> 2, "symbol"=> "COU" ],
        "CRC"=> [ "code"=> "CRC", "name"=> "Cost Rican Colon", "fractionSize"=> 2, "symbol"=> "₡" ],
        "CUC"=> [ "code"=> "CUC", "name"=> "Peso Convertible", "fractionSize"=> 2, "symbol"=> "CUC" ],
        "CUP"=> [ "code"=> "CUP", "name"=> "Cuban Peso", "fractionSize"=> 2, "symbol"=> '$MN' ],
        "CVE"=> [ "code"=> "CVE", "name"=> "Cabo Verde Escudo", "fractionSize"=> 2, "symbol"=> "esc" ],
        "CZK"=> [ "code"=> "CZK", "name"=> "Czech Koruna", "fractionSize"=> 2, "symbol"=> "Kč" ],
        "DJF"=> [ "code"=> "DJF", "name"=> "Djibouti Franc", "fractionSize"=> 0, "symbol"=> "Fdj" ],
        "DKK"=> [ "code"=> "DKK", "name"=> "Danish Krone", "fractionSize"=> 2, "symbol"=> "kr" ],
        "DOP"=> [ "code"=> "DOP", "name"=> "Dominican Peso", "fractionSize"=> 2, "symbol"=> "RD$" ],
        "DZD"=> [ "code"=> "DZD", "name"=> "Algerian Dinar", "fractionSize"=> 2, "symbol"=> ".د.ج" ],
        "EEK"=> [ "code"=> "EEK", "name"=> "Estonian Kroon", "fractionSize"=> 2, "symbol"=> "kr" ],
        "EGP"=> [ "code"=> "EGP", "name"=> "Egyptian Pound", "fractionSize"=> 2, "symbol"=> "£" ],
        "ERN"=> [ "code"=> "ERN", "name"=> "Nakfa", "fractionSize"=> 2, "symbol"=> "Nkf" ],
        "ETB"=> [ "code"=> "ETB", "name"=> "Ethiopian Birr", "fractionSize"=> 2, "symbol"=> "Br" ],
        "EUR"=> [ "code"=> "EUR", "name"=> "Euro", "fractionSize"=> 2, "symbol"=> "€" ],
        "FJD"=> [ "code"=> "FJD", "name"=> "Fiji Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "FKP"=> [ "code"=> "FKP", "name"=> "Falkland Islands Pound", "fractionSize"=> 2, "symbol"=> "£" ],
        "GBP"=> [ "code"=> "GBP", "name"=> "Pound Sterling", "fractionSize"=> 2, "symbol"=> "£" ],
        "GEL"=> [ "code"=> "GEL", "name"=> "Lari", "fractionSize"=> 2, "symbol"=> "GEL" ],
        "GGP"=> [ "code"=> "GGP", "name"=> "Guernsey Pound", "fractionSize"=> 2, "symbol"=> "£" ],
        "GHC"=> [ "code"=> "GHC", "name"=> "Ghanaian Cedi", "fractionSize"=> 2, "symbol"=> "¢" ],
        "GHS"=> [ "code"=> "GHS", "name"=> "Ghan Cedi", "fractionSize"=> 2, "symbol"=> "GH₵" ],
        "GIP"=> [ "code"=> "GIP", "name"=> "Gibraltar Pound", "fractionSize"=> 2, "symbol"=> "£" ],
        "GMD"=> [ "code"=> "GMD", "name"=> "Dalasi", "fractionSize"=> 2, "symbol"=> "D" ],
        "GNF"=> [ "code"=> "GNF", "name"=> "Guine Franc", "fractionSize"=> 0, "symbol"=> "GFr" ],
        "GTQ"=> [ "code"=> "GTQ", "name"=> "Quetzal", "fractionSize"=> 2, "symbol"=> "Q" ],
        "GYD"=> [ "code"=> "GYD", "name"=> "Guyan Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "HKD"=> [ "code"=> "HKD", "name"=> "Hong Kong Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "HNL"=> [ "code"=> "HNL", "name"=> "Lempira", "fractionSize"=> 2, "symbol"=> "L" ],
        "HRK"=> [ "code"=> "HRK", "name"=> "Croatian Kuna", "fractionSize"=> 2, "symbol"=> "kn" ],
        "HTG"=> [ "code"=> "HTG", "name"=> "Gourde", "fractionSize"=> 2, "symbol"=> "G" ],
        "HUF"=> [ "code"=> "HUF", "name"=> "Forint", "fractionSize"=> 0, "symbol"=> "Ft" ],
        "IDR"=> [ "code"=> "IDR", "name"=> "Rupiah", "fractionSize"=> 2, "symbol"=> "Rp" ],
        "ILS"=> [ "code"=> "ILS", "name"=> "New Israeli Sheqel", "fractionSize"=> 2, "symbol"=> "₪" ],
        "IMP"=> [ "code"=> "IMP", "name"=> "Manx Pound", "fractionSize"=> 2, "symbol"=> "£" ],
        "INR"=> [ "code"=> "INR", "name"=> "Indian Rupee", "fractionSize"=> 2, "symbol"=> "₹" ],
        "IQD"=> [ "code"=> "IQD", "name"=> "Iraqi Dinar", "fractionSize"=> 3, "symbol"=> ".د.ع" ],
        "IRR"=> [ "code"=> "IRR", "name"=> "Iranian Rial", "fractionSize"=> 0, "symbol"=> "﷼" ],
        "ISK"=> [ "code"=> "ISK", "name"=> "Iceland Krona", "fractionSize"=> 2, "symbol"=> "kr" ],
        "JEP"=> [ "code"=> "JEP", "name"=> "Jersey Pound", "fractionSize"=> 2, "symbol"=> "£" ],
        "JMD"=> [ "code"=> "JMD", "name"=> "Jamaican Dollar", "fractionSize"=> 2, "symbol"=> "J$" ],
        "JOD"=> [ "code"=> "JOD", "name"=> "Jordanian Dinar", "fractionSize"=> 3, "symbol"=> ".د.إ" ],
        "JPY"=> [ "code"=> "JPY", "name"=> "Yen", "fractionSize"=> 0, "symbol"=> "¥" ],
        "KES"=> [ "code"=> "KES", "name"=> "Kenyan Shilling", "fractionSize"=> 2, "symbol"=> "KSh" ],
        "KGS"=> [ "code"=> "KGS", "name"=> "Som", "fractionSize"=> 2, "symbol"=> "сом" ],
        "KHR"=> [ "code"=> "KHR", "name"=> "Riel", "fractionSize"=> 2, "symbol"=> "៛" ],
        "KMF"=> [ "code"=> "KMF", "name"=> "Comoro Franc", "fractionSize"=> 0, "symbol"=> "CF" ],
        "KPW"=> [ "code"=> "KPW", "name"=> "North Korean Won", "fractionSize"=> 0, "symbol"=> "₩" ],
        "KRW"=> [ "code"=> "KRW", "name"=> "Won", "fractionSize"=> 0, "symbol"=> "₩" ],
        "KWD"=> [ "code"=> "KWD", "name"=> "Kuwaiti Dinar", "fractionSize"=> 3, "symbol"=> ".د.ك" ],
        "KYD"=> [ "code"=> "KYD", "name"=> "Cayman Islands Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "KZT"=> [ "code"=> "KZT", "name"=> "Tenge", "fractionSize"=> 2, "symbol"=> "₸" ],
        "LAK"=> [ "code"=> "LAK", "name"=> "Kip", "fractionSize"=> 2, "symbol"=> "₭" ],
        "LBP"=> [ "code"=> "LBP", "name"=> "Lebanese Pound", "fractionSize"=> 2, "symbol"=> "£" ],
        "LKR"=> [ "code"=> "LKR", "name"=> "Sri Lank Rupee", "fractionSize"=> 2, "symbol"=> "₨" ],
        "LRD"=> [ "code"=> "LRD", "name"=> "Liberian Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "LSL"=> [ "code"=> "LSL", "name"=> "Loti", "fractionSize"=> 2, "symbol"=> "LSL" ],
        "LTL"=> [ "code"=> "LTL", "name"=> "Lithuanian Litas", "fractionSize"=> 2, "symbol"=> "Lt" ],
        "LVL"=> [ "code"=> "LVL", "name"=> "Latvian Lats", "fractionSize"=> 2, "symbol"=> "Ls" ],
        "LYD"=> [ "code"=> "LYD", "name"=> "Libyan Dinar", "fractionSize"=> 3, "symbol"=> ".د.ل" ],
        "MAD"=> [ "code"=> "MAD", "name"=> "Moroccan Dirham", "fractionSize"=> 2, "symbol"=> ".د.م" ],
        "MDL"=> [ "code"=> "MDL", "name"=> "Moldovan Leu", "fractionSize"=> 2, "symbol"=> "lei" ],
        "MGA"=> [ "code"=> "MGA", "name"=> "Malagasy ariary", "fractionSize"=> 1, "symbol"=> "Ar" ],
        "MKD"=> [ "code"=> "MKD", "name"=> "Denar", "fractionSize"=> 2, "symbol"=> "ден" ],
        "MMK"=> [ "code"=> "MMK", "name"=> "Kyat", "fractionSize"=> 2, "symbol"=> "K" ],
        "MNT"=> [ "code"=> "MNT", "name"=> "Tugrik", "fractionSize"=> 2, "symbol"=> "₮" ],
        "MOP"=> [ "code"=> "MOP", "name"=> "Pataca", "fractionSize"=> 2, "symbol"=> "MOP$" ],
        "MRO"=> [ "code"=> "MRO", "name"=> "Ouguiya", "fractionSize"=> 2, "symbol"=> "ouguiya" ],
        "MUR"=> [ "code"=> "MUR", "name"=> "Mauritius Rupee", "fractionSize"=> 2, "symbol"=> "₨" ],
        "MVR"=> [ "code"=> "MVR", "name"=> "Rufiyaa", "fractionSize"=> 2, "symbol"=> "MVR" ],
        "MWK"=> [ "code"=> "MWK", "name"=> "Kwacha", "fractionSize"=> 2, "symbol"=> "MK" ],
        "MXN"=> [ "code"=> "MXN", "name"=> "Mexican Peso", "fractionSize"=> 2, "symbol"=> "$" ],
        "MXV"=> [ "code"=> "MXV", "name"=> "Mexican Unidad de Inversion (UDI)", "fractionSize"=> 2, "symbol"=> "UDI" ],
        "MYR"=> [ "code"=> "MYR", "name"=> "Malaysian Ringgit", "fractionSize"=> 2, "symbol"=> "RM" ],
        "MZN"=> [ "code"=> "MZN", "name"=> "Mozambique Metical", "fractionSize"=> 2, "symbol"=> "MT" ],
        "NAD"=> [ "code"=> "NAD", "name"=> "Namibi Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "NGN"=> [ "code"=> "NGN", "name"=> "Naira", "fractionSize"=> 2, "symbol"=> "₦" ],
        "NIO"=> [ "code"=> "NIO", "name"=> "Cordob Oro", "fractionSize"=> 2, "symbol"=> "C$" ],
        "NOK"=> [ "code"=> "NOK", "name"=> "Norwegian Krone", "fractionSize"=> 2, "symbol"=> "kr" ],
        "NPR"=> [ "code"=> "NPR", "name"=> "Nepalese Rupee", "fractionSize"=> 2, "symbol"=> "₨" ],
        "NZD"=> [ "code"=> "NZD", "name"=> "New Zealand Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "OMR"=> [ "code"=> "OMR", "name"=> "Rial Omani", "fractionSize"=> 3, "symbol"=> "﷼" ],
        "PAB"=> [ "code"=> "PAB", "name"=> "Balboa", "fractionSize"=> 2, "symbol"=> "B/." ],
        "PEN"=> [ "code"=> "PEN", "name"=> "Nuevo Sol", "fractionSize"=> 2, "symbol"=> "S/" ],
        "PGK"=> [ "code"=> "PGK", "name"=> "Kina", "fractionSize"=> 2, "symbol"=> "K" ],
        "PHP"=> [ "code"=> "PHP", "name"=> "Philippine Peso", "fractionSize"=> 2, "symbol"=> "₱" ],
        "PKR"=> [ "code"=> "PKR", "name"=> "Pakistan Rupee", "fractionSize"=> 2, "symbol"=> "₨" ],
        "PLN"=> [ "code"=> "PLN", "name"=> "Zloty", "fractionSize"=> 2, "symbol"=> "zł" ],
        "PYG"=> [ "code"=> "PYG", "name"=> "Guarani", "fractionSize"=> 0, "symbol"=> "Gs" ],
        "QAR"=> [ "code"=> "QAR", "name"=> "Qatari Rial", "fractionSize"=> 2, "symbol"=> "﷼" ],
        "RON"=> [ "code"=> "RON", "name"=> "New Romanian Leu", "fractionSize"=> 2, "symbol"=> "lei" ],
        "RSD"=> [ "code"=> "RSD", "name"=> "Serbian Dinar", "fractionSize"=> 2, "symbol"=> "Дин." ],
        "RUB"=> [ "code"=> "RUB", "name"=> "Russian Ruble", "fractionSize"=> 2, "symbol"=> "₽" ],
        "RUR"=> [ "code"=> "RUR", "name"=> "Russian Ruble", "fractionSize"=> 2, "symbol"=> "₽" ],
        "RWF"=> [ "code"=> "RWF", "name"=> "Rwand Franc", "fractionSize"=> 0, "symbol"=> "R₣" ],
        "SAR"=> [ "code"=> "SAR", "name"=> "Saudi Riyal", "fractionSize"=> 2, "symbol"=> "﷼" ],
        "SBD"=> [ "code"=> "SBD", "name"=> "Solomon Islands Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "SCR"=> [ "code"=> "SCR", "name"=> "Seychelles Rupee", "fractionSize"=> 2, "symbol"=> "₨" ],
        "SDG"=> [ "code"=> "SDG", "name"=> "Sudanese Pound", "fractionSize"=> 2, "symbol"=> "SDG" ],
        "SEK"=> [ "code"=> "SEK", "name"=> "Swedish Krona", "fractionSize"=> 2, "symbol"=> "kr" ],
        "SGD"=> [ "code"=> "SGD", "name"=> "Singapore Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "SHP"=> [ "code"=> "SHP", "name"=> "Saint Helen Pound", "fractionSize"=> 2, "symbol"=> "£" ],
        "SLL"=> [ "code"=> "SLL", "name"=> "Leone", "fractionSize"=> 2, "symbol"=> "Le" ],
        "SOS"=> [ "code"=> "SOS", "name"=> "Somali Shilling", "fractionSize"=> 2, "symbol"=> "S" ],
        "SRD"=> [ "code"=> "SRD", "name"=> "Surinam Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "SSP"=> [ "code"=> "SSP", "name"=> "South Sudanese Pound", "fractionSize"=> 2, "symbol"=> "SS£" ],
        "STD"=> [ "code"=> "STD", "name"=> "Dobra", "fractionSize"=> 2, "symbol"=> "Db" ],
        "SVC"=> [ "code"=> "SVC", "name"=> "El Salvador Colon", "fractionSize"=> 2, "symbol"=> "$" ],
        "SYP"=> [ "code"=> "SYP", "name"=> "Syrian Pound", "fractionSize"=> 2, "symbol"=> "£" ],
        "SZL"=> [ "code"=> "SZL", "name"=> "Lilangeni", "fractionSize"=> 2, "symbol"=> "L" ],
        "THB"=> [ "code"=> "THB", "name"=> "Baht", "fractionSize"=> 2, "symbol"=> "฿" ],
        "TJS"=> [ "code"=> "TJS", "name"=> "Somoni", "fractionSize"=> 2, "symbol"=> "SM" ],
        "TMT"=> [ "code"=> "TMT", "name"=> "Turkmenistan New Manat", "fractionSize"=> 2, "symbol"=> "T" ],
        "TND"=> [ "code"=> "TND", "name"=> "Tunisian Dinar", "fractionSize"=> 3, "symbol"=> ".د.ت" ],
        "TOP"=> [ "code"=> "TOP", "name"=> "Pa’anga", "fractionSize"=> 2, "symbol"=> "T$" ],
        "TRL"=> [ "code"=> "TRL", "name"=> "Turkish Lira", "fractionSize"=> 2, "symbol"=> "₤" ],
        "TRY"=> [ "code"=> "TRY", "name"=> "Turkish Lira", "fractionSize"=> 2, "symbol"=> "₺" ],
        "TTD"=> [ "code"=> "TTD", "name"=> "Trinidad and Tobago Dollar", "fractionSize"=> 2, "symbol"=> "TT$" ],
        "TWD"=> [ "code"=> "TWD", "name"=> "New Taiwan Dollar", "fractionSize"=> 0, "symbol"=> "NT$" ],
        "TZS"=> [ "code"=> "TZS", "name"=> "Tanzanian Shilling", "fractionSize"=> 0, "symbol"=> "TSh" ],
        "UAH"=> [ "code"=> "UAH", "name"=> "Hryvnia", "fractionSize"=> 2, "symbol"=> "₴" ],
        "UGX"=> [ "code"=> "UGX", "name"=> "Ugand Shilling", "fractionSize"=> 0, "symbol"=> "USh" ],
        "USD"=> [ "code"=> "USD", "name"=> "US Dollar", "fractionSize"=> 2, "symbol"=> "US$" ],
        "USN"=> [ "code"=> "USN", "name"=> "US Dollar (Next day)", "fractionSize"=> 2, "symbol"=> "$" ],
        "UYI"=> [ "code"=> "UYI", "name"=> "Uruguay Peso en Unidades Indexadas (URUIURUI)", "fractionSize"=> 0, "symbol"=> '$U' ],
        "UYU"=> [ "code"=> "UYU", "name"=> "Peso Uruguayo", "fractionSize"=> 0, "symbol"=> '$U' ],
        "UZS"=> [ "code"=> "UZS", "name"=> "Uzbekistan Sum", "fractionSize"=> 2, "symbol"=> "so’m" ],
        "VEF"=> [ "code"=> "VEF", "name"=> "Bolivar", "fractionSize"=> 2, "symbol"=> "Bs" ],
        "VES"=> [ "code"=> "VES", "name"=> "Bolivar", "fractionSize"=> 2, "symbol"=> "Bs" ],
        "VND"=> [ "code"=> "VND", "name"=> "Dong", "fractionSize"=> 0, "symbol"=> "₫" ],
        "VUV"=> [ "code"=> "VUV", "name"=> "Vatu", "fractionSize"=> 0, "symbol"=> "VT" ],
        "WST"=> [ "code"=> "WST", "name"=> "Tala", "fractionSize"=> 2, "symbol"=> "WS$" ],
        "XAF"=> [ "code"=> "XAF", "name"=> "CFA Franc BEAC", "fractionSize"=> 0, "symbol"=> "FCFA" ],
        "XCD"=> [ "code"=> "XCD", "name"=> "East Caribbean Dollar", "fractionSize"=> 2, "symbol"=> "$" ],
        "XDR"=> [ "code"=> "XDR", "name"=> "SDR (Special Drawing Right)", "fractionSize"=> 0, "symbol"=> "SDR" ],
        "XOF"=> [ "code"=> "XOF", "name"=> "CFA Franc BCEAO", "fractionSize"=> 0, "symbol"=> "CFA" ],
        "XPF"=> [ "code"=> "XPF", "name"=> "CFP Franc", "fractionSize"=> 0, "symbol"=> "₣" ],
        "XSU"=> [ "code"=> "XSU", "name"=> "Sucre", "fractionSize"=> 0, "symbol"=> "XSU" ],
        "XUA"=> [ "code"=> "XUA", "name"=> "ADB Unit of Account", "fractionSize"=> 0, "symbol"=> "XUA" ],
        "YER"=> [ "code"=> "YER", "name"=> "Yemeni Rial", "fractionSize"=> 2, "symbol"=> "﷼" ],
        "ZAR"=> [ "code"=> "ZAR", "name"=> "Rand", "fractionSize"=> 2, "symbol"=> "R" ],
        "ZMW"=> [ "code"=> "ZMW", "name"=> "Zambian Kwacha", "fractionSize"=> 2, "symbol"=> "K" ],
        "ZWD"=> [ "code"=> "ZWD", "name"=> "Zimbabwe Dollar", "fractionSize"=> 2, "symbol"=> "Z$" ],
        "ZWL"=> [ "code"=> "ZWL", "name"=> "Zimbabwe Dollar", "fractionSize"=> 2, "symbol"=> "Z$" ],
        "BTC"=> [ "code"=> "BTC", "name"=> "BTC", "fractionSize"=> 4, "symbol"=> "₿" ],
        "ETH"=> [ "code"=> "ETH", "name"=> "ETH", "fractionSize"=> 4, "symbol"=> "Ξ" ],
        "LTC"=> [ "code"=> "LTC", "name"=> "LTC", "fractionSize"=> 4, "symbol"=> "Ł" ]
    ];
    var $timezone = [
        ["key"=>"Pacific/Midway","value"=>"(GMT-11:00) Midway Island, Samoa"],
        ["key"=>"America/Adak","value"=>"(GMT-10:00) Hawaii-Aleutian"],
        ["key"=>"Etc/GMT+10","value"=>"(GMT-10:00) Hawaii"],
        ["key"=>"Pacific/Marquesas","value"=>"(GMT-09:30) Marquesas Islands"],
        ["key"=>"Pacific/Gambier","value"=>"(GMT-09:00) Gambier Islands"],
        ["key"=>"America/Anchorage","value"=>"(GMT-09:00) Alaska"],
        ["key"=>"America/Ensenada","value"=>"(GMT-08:00) Tijuana, Baja California"],
        ["key"=>"Etc/GMT+8","value"=>"(GMT-08:00) Pitcairn Islands"],
        ["key"=>"America/Los_Angeles","value"=>"(GMT-08:00) Pacific Time (US & Canada)"],
        ["key"=>"America/Denver","value"=>"(GMT-07:00) Mountain Time (US & Canada)"],
        ["key"=>"America/Chihuahua","value"=>"(GMT-07:00) Chihuahua, La Paz, Mazatlan"],
        ["key"=>"America/Dawson_Creek","value"=>"(GMT-07:00) Arizona"],
        ["key"=>"America/Belize","value"=>"(GMT-06:00) Saskatchewan, Central America"],
        ["key"=>"America/Cancun","value"=>"(GMT-06:00) Guadalajara, Mexico City, Monterrey"],
        ["key"=>"Chile/EasterIsland","value"=>"(GMT-06:00) Easter Island"],
        ["key"=>"America/Chicago","value"=>"(GMT-06:00) Central Time (US & Canada)"],
        ["key"=>"America/New_York","value"=>"(GMT-05:00) Eastern Time (US & Canada)"],
        ["key"=>"America/Havana","value"=>"(GMT-05:00) Cuba"],
        ["key"=>"America/Bogota","value"=>"(GMT-05:00) Bogota, Lima, Quito, Rio Branco"],
        ["key"=>"America/Caracas","value"=>"(GMT-04:30) Caracas"],
        ["key"=>"America/Santiago","value"=>"(GMT-04:00) Santiago"],
        ["key"=>"America/La_Paz","value"=>"(GMT-04:00) La Paz"],
        ["key"=>"Atlantic/Stanley","value"=>"(GMT-04:00) Faukland Islands"],
        ["key"=>"America/Campo_Grande","value"=>"(GMT-04:00) Brazil"],
        ["key"=>"America/Goose_Bay","value"=>"(GMT-04:00) Atlantic Time (Goose Bay)"],
        ["key"=>"America/Glace_Bay","value"=>"(GMT-04:00) Atlantic Time (Canada)"],
        ["key"=>"America/St_Johns","value"=>"(GMT-03:30) Newfoundland"],
        ["key"=>"America/Araguaina","value"=>"(GMT-03:00) UTC-3"],
        ["key"=>"America/Montevideo","value"=>"(GMT-03:00) Montevideo"],
        ["key"=>"America/Miquelon","value"=>"(GMT-03:00) Miquelon, St. Pierre"],
        ["key"=>"America/Godthab","value"=>"(GMT-03:00) Greenland"],
        ["key"=>"America/Argentina/Buenos_Aires","value"=>"(GMT-03:00) Buenos Aires"],
        ["key"=>"America/Sao_Paulo","value"=>"(GMT-03:00) Brasilia"],
        ["key"=>"America/Noronha","value"=>"(GMT-02:00) Mid-Atlantic"],
        ["key"=>"Atlantic/Cape_Verde","value"=>"(GMT-01:00) Cape Verde Is."],
        ["key"=>"Atlantic/Azores","value"=>"(GMT-01:00) Azores"],
        ["key"=>"Europe/Belfast","value"=>"(GMT) Greenwich Mean Time : Belfast"],
        ["key"=>"Europe/Dublin","value"=>"(GMT) Greenwich Mean Time : Dublin"],
        ["key"=>"Europe/Lisbon","value"=>"(GMT) Greenwich Mean Time : Lisbon"],
        ["key"=>"Europe/London","value"=>"(GMT) Greenwich Mean Time : London"],
        ["key"=>"Africa/Abidjan","value"=>"(GMT) Monrovia, Reykjavik"],
        ["key"=>"Europe/Amsterdam","value"=>"(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna"],
        ["key"=>"Europe/Belgrade","value"=>"(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague"],
        ["key"=>"Europe/Brussels","value"=>"(GMT+01:00) Brussels, Copenhagen, Madrid, Paris"],
        ["key"=>"Africa/Algiers","value"=>"(GMT+01:00) West Central Africa"],
        ["key"=>"Africa/Windhoek","value"=>"(GMT+01:00) Windhoek"],
        ["key"=>"Asia/Beirut","value"=>"(GMT+02:00) Beirut"],
        ["key"=>"Africa/Cairo","value"=>"(GMT+02:00) Cairo"],
        ["key"=>"Asia/Gaza","value"=>"(GMT+02:00) Gaza"],
        ["key"=>"Africa/Blantyre","value"=>"(GMT+02:00) Harare, Pretoria"],
        ["key"=>"Asia/Jerusalem","value"=>"(GMT+02:00) Jerusalem"],
        ["key"=>"Europe/Minsk","value"=>"(GMT+02:00) Minsk"],
        ["key"=>"Asia/Damascus","value"=>"(GMT+02:00) Syria"],
        ["key"=>"Europe/Moscow","value"=>"(GMT+03:00) Moscow, St. Petersburg, Volgograd"],
        ["key"=>"Africa/Addis_Ababa","value"=>"(GMT+03:00) Nairobi"],
        ["key"=>"Asia/Tehran","value"=>"(GMT+03:30) Tehran"],
        ["key"=>"Asia/Dubai","value"=>"(GMT+04:00) Abu Dhabi, Muscat"],
        ["key"=>"Asia/Yerevan","value"=>"(GMT+04:00) Yerevan"],
        ["key"=>"Asia/Kabul","value"=>"(GMT+04:30) Kabul"],
        ["key"=>"Asia/Yekaterinburg","value"=>"(GMT+05:00) Ekaterinburg"],
        ["key"=>"Asia/Tashkent","value"=>"(GMT+05:00) Tashkent"],
        ["key"=>"Asia/Kolkata","value"=>"(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi"],
        ["key"=>"Asia/Katmandu","value"=>"(GMT+05:45) Kathmandu"],
        ["key"=>"Asia/Dhaka","value"=>"(GMT+06:00) Astana, Dhaka"],
        ["key"=>"Asia/Novosibirsk","value"=>"(GMT+06:00) Novosibirsk"],
        ["key"=>"Asia/Rangoon","value"=>"(GMT+06:30) Yangon (Rangoon)"],
        ["key"=>"Asia/Bangkok","value"=>"(GMT+07:00) Bangkok, Hanoi, Jakarta"],
        ["key"=>"Asia/Krasnoyarsk","value"=>"(GMT+07:00) Krasnoyarsk"],
        ["key"=>"Asia/Hong_Kong","value"=>"(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi"],
        ["key"=>"Asia/Irkutsk","value"=>"(GMT+08:00) Irkutsk, Ulaan Bataar"],
        ["key"=>"Australia/Perth","value"=>"(GMT+08:00) Perth"],
        ["key"=>"Australia/Eucla","value"=>"(GMT+08:45) Eucla"],
        ["key"=>"Asia/Tokyo","value"=>"(GMT+09:00) Osaka, Sapporo, Tokyo"],
        ["key"=>"Asia/Seoul","value"=>"(GMT+09:00) Seoul"],
        ["key"=>"Asia/Yakutsk","value"=>"(GMT+09:00) Yakutsk"],
        ["key"=>"Australia/Adelaide","value"=>"(GMT+09:30) Adelaide"],
        ["key"=>"Australia/Darwin","value"=>"(GMT+09:30) Darwin"],
        ["key"=>"Australia/Brisbane","value"=>"(GMT+10:00) Brisbane"],
        ["key"=>"Australia/Hobart","value"=>"(GMT+10:00) Hobart"],
        ["key"=>"Asia/Vladivostok","value"=>"(GMT+10:00) Vladivostok"],
        ["key"=>"Australia/Lord_Howe","value"=>"(GMT+10:30) Lord Howe Island"],
        ["key"=>"Etc/GMT-11","value"=>"(GMT+11:00) Solomon Is., New Caledonia"],
        ["key"=>"Asia/Magadan","value"=>"(GMT+11:00) Magadan"],
        ["key"=>"Pacific/Norfolk","value"=>"(GMT+11:30) Norfolk Island"],
        ["key"=>"Asia/Anadyr","value"=>"(GMT+12:00) Anadyr, Kamchatka"],
        ["key"=>"Pacific/Auckland","value"=>"(GMT+12:00) Auckland, Wellington"],
        ["key"=>"Etc/GMT-12","value"=>"(GMT+12:00) Fiji, Kamchatka, Marshall Is."],
        ["key"=>"Pacific/Chatham","value"=>"(GMT+12:45) Chatham Islands"],
        ["key"=>"Pacific/Tongatapu","value"=>"(GMT+13:00) Nuku'alofa"],
        ["key"=>"Pacific/Kiritimati","value"=>"(GMT+14:00) Kiritimati"]
    ];

    public function __construct()
    {
		if(app('request')->header('Authorization') != "" || \Input::has('token')) { $this->middleware('jwt.auth'); }
        else { $this->middleware('authApplication'); }

		$this->panelInit = new \DashboardInit();
		$this->data['panelInit'] = $this->panelInit;
		$this->data['breadcrumb']['Settings'] = \URL::to('/dashboard/languages');
		$this->data['users'] = $this->panelInit->getAuthUser();
		if(!isset($this->data['users']->id)) { return \Redirect::to('/'); }
    }

    public function getCitites()
    {
        $state = \Input::get('state'); $country = \Input::get('country');
        $cities = City::select('id', 'name')->where('state_id', $state)->where('country_id', $country)->get()->toArray();
        $toReturn['status'] = "success";
        $toReturn['cities'] = $cities;
        return $toReturn;
    }
    
    public function loadSettings()
    {
        $currentSettings = Setting::pluck('fieldValue', 'fieldName')->toArray();
        $toAddArray = [];
        if( !array_key_exists('siteTitle', $currentSettings) ) { $currentSettings['siteTitle'] = ""; $toAddArray[] = ["fieldName" => "siteTitle", "fieldValue" => ""]; }
        if( !array_key_exists('footer', $currentSettings) ) { $currentSettings['footer'] = ""; $toAddArray[] = ["fieldName" => "footer", "fieldValue" => ""]; }
        if( !array_key_exists('startDate', $currentSettings) ) { $currentSettings['startDate'] = ""; $toAddArray[] = ["fieldName" => "startDate", "fieldValue" => ""]; }
        if( !array_key_exists('estabDate', $currentSettings) ) { $currentSettings['estabDate'] = ""; $toAddArray[] = ["fieldName" => "estabDate", "fieldValue" => ""]; }
        if( !array_key_exists('schoolMoto', $currentSettings) ) { $currentSettings['schoolMoto'] = ""; $toAddArray[] = ["fieldName" => "schoolMoto", "fieldValue" => ""]; }
        if( !array_key_exists('affilBy', $currentSettings) ) { $currentSettings['affilBy'] = ""; $toAddArray[] = ["fieldName" => "affilBy", "fieldValue" => ""]; }
        if( !array_key_exists('affilNo', $currentSettings) ) { $currentSettings['affilNo'] = ""; $toAddArray[] = ["fieldName" => "affilNo", "fieldValue" => ""]; }
        if( !array_key_exists('regisNo', $currentSettings) ) { $currentSettings['regisNo'] = ""; $toAddArray[] = ["fieldName" => "regisNo", "fieldValue" => ""]; }
        if( !array_key_exists('siteLogo', $currentSettings) ) { $currentSettings['siteLogo'] = ""; $toAddArray[] = ["fieldName" => "siteLogo", "fieldValue" => ""]; }
        if( !array_key_exists('board', $currentSettings) ) { $currentSettings['board'] = ""; $toAddArray[] = ["fieldName" => "board", "fieldValue" => ""]; }
        if( !array_key_exists('aboutUs', $currentSettings) ) { $currentSettings['aboutUs'] = ""; $toAddArray[] = ["fieldName" => "aboutUs", "fieldValue" => ""]; }
        if( !array_key_exists('systemEmail', $currentSettings) ) { $currentSettings['systemEmail'] = ""; $toAddArray[] = ["fieldName" => "systemEmail", "fieldValue" => ""]; }
        if( !array_key_exists('country', $currentSettings) ) { $currentSettings['country'] = ""; $toAddArray[] = ["fieldName" => "country", "fieldValue" => ""]; }
        if( !array_key_exists('State', $currentSettings) ) { $currentSettings['State'] = ""; $toAddArray[] = ["fieldName" => "State", "fieldValue" => ""]; }
        if( !array_key_exists('City', $currentSettings) ) { $currentSettings['City'] = ""; $toAddArray[] = ["fieldName" => "City", "fieldValue" => ""]; }
        if( !array_key_exists('District', $currentSettings) ) { $currentSettings['District'] = ""; $toAddArray[] = ["fieldName" => "District", "fieldValue" => ""]; }
        if( !array_key_exists('address', $currentSettings) ) { $currentSettings['address'] = ""; $toAddArray[] = ["fieldName" => "address", "fieldValue" => ""]; }
        if( !array_key_exists('pin', $currentSettings) ) { $currentSettings['pin'] = ""; $toAddArray[] = ["fieldName" => "pin", "fieldValue" => ""]; }
        if( !array_key_exists('mobile1', $currentSettings) ) { $currentSettings['mobile1'] = ""; $toAddArray[] = ["fieldName" => "mobile1", "fieldValue" => ""]; }
        if( !array_key_exists('mobile2', $currentSettings) ) { $currentSettings['mobile2'] = ""; $toAddArray[] = ["fieldName" => "mobile2", "fieldValue" => ""]; }
        if( !array_key_exists('landLine', $currentSettings) ) { $currentSettings['landLine'] = ""; $toAddArray[] = ["fieldName" => "landLine", "fieldValue" => ""]; }
        if( !array_key_exists('faxNo', $currentSettings) ) { $currentSettings['faxNo'] = ""; $toAddArray[] = ["fieldName" => "faxNo", "fieldValue" => ""]; }
        if( !array_key_exists('timezone', $currentSettings) ) { $currentSettings['timezone'] = ""; $toAddArray[] = ["fieldName" => "timezone", "fieldValue" => ""]; }
        if( !array_key_exists('currency_symbol', $currentSettings) ) { $currentSettings['currency_symbol'] = ""; $toAddArray[] = ["fieldName" => "currency_symbol", "fieldValue" => ""]; }
        if( !array_key_exists('currency_code', $currentSettings) ) { $currentSettings['currency_code'] = ""; $toAddArray[] = ["fieldName" => "currency_code", "fieldValue" => ""]; }
        
        if( count( $toAddArray ) != 0 ) { Setting::insert( $toAddArray ); }
        
        $toReturn = [];
        $settingsArray = [
            'siteTitle' => $currentSettings['siteTitle'],
            'footer' => $currentSettings['footer'],
            'startDate' => $currentSettings['startDate'],
            'estabDate' => $currentSettings['estabDate'],
            'schoolMoto' => $currentSettings['schoolMoto'],
            'affilBy' => $currentSettings['affilBy'],
            'affilNo' => $currentSettings['affilNo'],
            'regisNo' => $currentSettings['regisNo'],
            'siteLogo' => $currentSettings['siteLogo'],
            'board' => $currentSettings['board'],
            'aboutUs' => $currentSettings['aboutUs'],
            'systemEmail' => $currentSettings['systemEmail'],
            'country' => $currentSettings['country'],
            'State' => $currentSettings['State'],
            'City' => $currentSettings['City'],
            'District' => $currentSettings['District'],
            'address' => $currentSettings['address'],
            'pin' => $currentSettings['pin'],
            'mobile1' => $currentSettings['mobile1'],
            'mobile2' => $currentSettings['mobile2'],
            'landLine' => $currentSettings['landLine'],
            'faxNo' => $currentSettings['faxNo'],
            'timezone' => $currentSettings['timezone'],
            'currency_symbol' => $currentSettings['currency_symbol'],
            'currency_code' => $currentSettings['currency_code']
        ];
        $toReturn['status'] = "success";
        $toReturn['countries'] = Main::getMixedCountriesStates();
        $toReturn['currencies'] = $this->currencies;
        $toReturn['timezones'] = $this->timezone;
        $toReturn['settings'] = $settingsArray;
        return $toReturn;
    }
    
    public function saveSettings()
    {
        $siteTitle = \Input::get('siteTitle');
        $footer = \Input::get('footer');
        $startDate = \Input::get('startDate');
        $estabDate = \Input::get('estabDate');
        $schoolMoto = \Input::get('schoolMoto');
        $affilBy = \Input::get('affilBy');
        $affilNo = \Input::get('affilNo');
        $regisNo = \Input::get('regisNo');
        $board = \Input::get('board');
        $aboutUs = \Input::get('aboutUs');
        $systemEmail = \Input::get('systemEmail');
        $country = \Input::get('country');
        $state = \Input::get('state');
        $city = \Input::get('city');
        $district = \Input::get('district');
        $address = \Input::get('address');
        $pin = \Input::get('pin');
        $mobile1 = \Input::get('mobile1');
        $mobile2 = \Input::get('mobile2');
        $landLine = \Input::get('landLine');
        $faxNo = \Input::get('faxNo');
        $timezone = \Input::get('timezone');
        $currency_symbol = \Input::get('currency_symbol');
        $currency_code = \Input::get('currency_code');
        
        $siteTitle_q = Setting::where('fieldName', 'siteTitle')->first();
        if( !$siteTitle_q ) { $siteTitle_q = new Setting(); $siteTitle_q->fieldName = "siteTitle"; }
        $siteTitle_q->fieldValue = $siteTitle;
        $siteTitle_q->save();
        $footer_q = Setting::where('fieldName', 'footer')->first();
        if( !$footer_q ) { $footer_q = new Setting(); $footer_q->fieldName = "footer"; }
        $footer_q->fieldValue = $footer;
        $footer_q->save();
        $startDate_q = Setting::where('fieldName', 'startDate')->first();
        if( !$startDate_q ) { $startDate_q = new Setting(); $startDate_q->fieldName = "startDate"; }
        $startDate_q->fieldValue = $startDate;
        $startDate_q->save();
        $estabDate_q = Setting::where('fieldName', 'estabDate')->first();
        if( !$estabDate_q ) { $estabDate_q = new Setting(); $estabDate_q->fieldName = "estabDate"; }
        $estabDate_q->fieldValue = $estabDate;
        $estabDate_q->save();
        $schoolMoto_q = Setting::where('fieldName', 'schoolMoto')->first();
        if( !$schoolMoto_q ) { $schoolMoto_q = new Setting(); $schoolMoto_q->fieldName = "schoolMoto"; }
        $schoolMoto_q->fieldValue = $schoolMoto;
        $schoolMoto_q->save();
        $affilBy_q = Setting::where('fieldName', 'affilBy')->first();
        if( !$affilBy_q ) { $affilBy_q = new Setting(); $affilBy_q->fieldName = "affilBy"; }
        $affilBy_q->fieldValue = $affilBy;
        $affilBy_q->save();
        $affilNo_q = Setting::where('fieldName', 'affilNo')->first();
        if( !$affilNo_q ) { $affilNo_q = new Setting(); $affilNo_q->fieldName = "affilNo"; }
        $affilNo_q->fieldValue = $affilNo;
        $affilNo_q->save();
        $regisNo_q = Setting::where('fieldName', 'regisNo')->first();
        if( !$regisNo_q ) { $regisNo_q = new Setting(); $regisNo_q->fieldName = "regisNo"; }
        $regisNo_q->fieldValue = $regisNo;
        $regisNo_q->save();
        $board_q = Setting::where('fieldName', 'board')->first();
        if( !$board_q ) { $board_q = new Setting(); $board_q->fieldName = "board"; }
        $board_q->fieldValue = $board;
        $board_q->save();
        $aboutUs_q = Setting::where('fieldName', 'aboutUs')->first();
        if( !$aboutUs_q ) { $aboutUs_q = new Setting(); $aboutUs_q->fieldName = "aboutUs"; }
        $aboutUs_q->fieldValue = $aboutUs;
        $aboutUs_q->save();
        $systemEmail_q = Setting::where('fieldName', 'systemEmail')->first();
        if( !$systemEmail_q ) { $systemEmail_q = new Setting(); $systemEmail_q->fieldName = "systemEmail"; }
        $systemEmail_q->fieldValue = $systemEmail;
        $systemEmail_q->save();
        $country_q = Setting::where('fieldName', 'country')->first();
        if( !$country_q ) { $country_q = new Setting(); $country_q->fieldName = "country"; }
        $country_q->fieldValue = $country;
        $country_q->save();
        $State_q = Setting::where('fieldName', 'State')->first();
        if( !$State_q ) { $State_q = new Setting(); $State_q->fieldName = "State"; }
        $State_q->fieldValue = $state;
        $State_q->save();
        $City_q = Setting::where('fieldName', 'City')->first();
        if( !$City_q ) { $City_q = new Setting(); $City_q->fieldName = "City"; }
        $City_q->fieldValue = $city;
        $City_q->save();
        $District_q = Setting::where('fieldName', 'District')->first();
        if( !$District_q ) { $District_q = new Setting(); $District_q->fieldName = "District"; }
        $District_q->fieldValue = $district;
        $District_q->save();
        $address_q = Setting::where('fieldName', 'address')->first();
        if( !$address_q ) { $address_q = new Setting(); $address_q->fieldName = "address"; }
        $address_q->fieldValue = $address;
        $address_q->save();
        $pin_q = Setting::where('fieldName', 'pin')->first();
        if( !$pin_q ) { $pin_q = new Setting(); $pin_q->fieldName = "pin"; }
        $pin_q->fieldValue = $pin;
        $pin_q->save();
        $mobile1_q = Setting::where('fieldName', 'mobile1')->first();
        if( !$mobile1_q ) { $mobile1_q = new Setting(); $mobile1_q->fieldName = "mobile1"; }
        $mobile1_q->fieldValue = $mobile1;
        $mobile1_q->save();
        $mobile2_q = Setting::where('fieldName', 'mobile2')->first();
        if( !$mobile2_q ) { $mobile2_q = new Setting(); $mobile2_q->fieldName = "mobile2"; }
        $mobile2_q->fieldValue = $mobile2;
        $mobile2_q->save();
        $landLine_q = Setting::where('fieldName', 'landLine')->first();
        if( !$landLine_q ) { $landLine_q = new Setting(); $landLine_q->fieldName = "landLine"; }
        $landLine_q->fieldValue = $landLine;
        $landLine_q->save();
        $faxNo_q = Setting::where('fieldName', 'faxNo')->first();
        if( !$faxNo_q ) { $faxNo_q = new Setting(); $faxNo_q->fieldName = "faxNo"; }
        $faxNo_q->fieldValue = $faxNo;
        $faxNo_q->save();
        $timezone_q = Setting::where('fieldName', 'timezone')->first();
        if( !$timezone_q ) { $timezone_q = new Setting(); $timezone_q->fieldName = "timezone"; }
        $timezone_q->fieldValue = $timezone;
        $timezone_q->save();
        $currency_symbol_q = Setting::where('fieldName', 'currency_symbol')->first();
        if( !$currency_symbol_q ) { $currency_symbol_q = new Setting(); $currency_symbol_q->fieldName = "currency_symbol"; }
        $currency_symbol_q->fieldValue = $currency_symbol;
        $currency_symbol_q->save();
        $currency_code_q = Setting::where('fieldName', 'currency_code')->first();
        if( !$currency_code_q ) { $currency_code_q = new Setting(); $currency_code_q->fieldName = "currency_code"; }
        $currency_code_q->fieldValue = $currency_code;
        $currency_code_q->save();

        return $this->panelInit->apiOutput(true, "School Settings", "Settings saved successfully");
    }
}