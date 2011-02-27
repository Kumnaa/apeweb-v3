<?php
/*
  Streamline esolutions client

  @author Ben Bowtell

  @date 22-Nov-2009

  (c) 2009 by http://www.apetechnologies.net/

  contact: ben@apetechnologies.net

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class streamline {

    public $order_number = '';
    private $pounds;
    private $pence;
    public $message = '';

    public static function gen_mac($orderKey, $paymentAmount, $paymentCurrency, $paymentStatus, $mac_secret) {
        return md5($orderKey . $paymentAmount . $paymentCurrency . $paymentStatus . $mac_secret);
    }

    public function __construct($order_number, $pounds, $pence) {
        $pence = str_pad($pence, 2, "0", STR_PAD_LEFT);
        if (strlen($pence) != 2) {
            throw new Exception("Incorrect pence value: " . $pence);
        }
        $this->order_number = $order_number;
        $this->pounds = $pounds;
        $this->pence = $pence;
    }

    public function generate_payment($order_type, $email = '', $passed_message = '') {
        $imp = new DOMImplementation;
        $dtd = $imp->createDocumentType('paymentService', '-//Streamline-esolutions/DTD Streamline-esolutions PaymentService v1//EN', 'http://dtd.streamline-esolutions.com/paymentService_v1.dtd');
        $doc = $imp->createDocument("", "", $dtd);
        $root = $doc->createElement('paymentService');
        $root = $doc->appendChild($root);
        $root->setAttribute('version', '1.4');
        $root->setAttribute('merchantCode', streamline_config::merchant_id());
        $submit = $doc->createElement('submit');
        $submit = $root->appendChild($submit);
        $order = $doc->createElement('order');
        $order = $submit->appendChild($order);
        $order->setAttribute('orderCode', $this->order_number);
        $description = $doc->createElement('description');
        $description = $order->appendChild($description);
        $value = $doc->createTextNode($order_type);
        $value = $description->appendChild($value);
        $amount = $doc->createElement('amount');
        $amount = $order->appendChild($amount);
        $amount->setAttribute('value', htmlspecialchars($this->pounds) . htmlspecialchars($this->pence));
        $amount->setAttribute('currencyCode', 'GBP');
        $amount->setAttribute('exponent', '2');
        $orderContent = $doc->createElement('orderContent');
        $orderContent = $order->appendchild($orderContent);
        $value = $doc->createTextNode('You are here to make a payment to ' . streamline_config::payee_name());
        $value = $orderContent->appendChild($value);
        $paymentMethodMask = $doc->createElement('paymentMethodMask');
        $paymentMethodMask = $order->appendChild($paymentMethodMask);
        $visa = $doc->createElement('include');
        $visa = $paymentMethodMask->appendChild($visa);
        $visa->setAttribute('code', 'ALL');
        $shopper = $doc->createElement('shopper');
        $shopper = $order->appendChild($shopper);
        $shopperEmailAddress = $doc->createElement('shopperEmailAddress');
        $shopperEmailAddress = $shopper->appendChild($shopperEmailAddress);
        $value = $doc->createTextNode($email);
        $value = $shopperEmailAddress->appendChild($value);
        $data = $doc->saveXML();
        try {
            $sent = $this->streamline_send($data);
        } catch (Exception $e) {
            throw $e;
        }
        $msg = 'New streamline application<br /><br />
            This may not result in a completed payment - please check Streamline eSolutions emails for completed payment<br /><br />';
        apetech::send_mail(streamline_config::mail_to(), 'New Streamline Application', '<html><body>' . $msg . '<br />' . $passed_message . '</body></html>', config::smtp_sender(), config::smtp_sender());
        $this->message = htmlspecialchars($sent);
    }

    private function streamline_send($data) {
        // create a new cURL resource
        $ch = curl_init();
        if (streamline_config::username_password() != '') {
            curl_setopt($ch, CURLOPT_USERPWD, streamline_config::username_password());
        }
        if (streamline_config::http() == 'https://') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, ARRAY('Content-Type: text/xml'));
        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, streamline_config::http() . streamline_config::host() . streamline_config::path());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // grab URL and pass it to the browser
        $return = curl_exec($ch);

        // close cURL resource, and free up system resources
        curl_close($ch);

        if ($return === false) {
            $error = curl_error($ch);
            apetech::error_email(array('CURL ERROR: ' . $error));
            throw new Exception($error);
        } else {
            $doc = new DOMDocument();
            $doc->loadXML($return);
            $errors = $doc->getElementsByTagName('error');
            if ($errors->length > 0) {
                apetech::error_email(array('STREAMLINE ERROR: ' . $errors->item(0)->nodeValue, htmlspecialchars($return)));
                throw new Exception("Xml error: " . $errors->item(0)->nodeValue);
            } else {
                $redirect_array = $doc->getElementsByTagName('reference');
                return $redirect_array->item(0)->nodeValue . '&successURL=' . urlencode(streamline_config::success_url()) . '&failureURL=' . urlencode(streamline_config::failure_url());
            }
        }
    }

}

?>