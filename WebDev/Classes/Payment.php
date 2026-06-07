<?php

/**

 * KLASA PAYMENT

 * ==============

 * Menaxhon integrimet me Stripe dhe PayPal.

 *

 * SHPJEGIM për fillestarët:

 * - Stripe dhe PayPal janë platforma për pagesa online

 * - API Keys: Çelësa sekretë që autorizojnë aplikacionin

 * - Webhook: Njoftim automatik kur ndodh një event (p.sh. pagesë e suksesshme)

 *

 * KONFIGURIMI:

 * 1. Krijo llogari në stripe.com dhe/ose paypal.com

 * 2. Merr API keys nga dashboard

 * 3. Vendosi ato në config/database.php

 */

 

class Payment

{

    private Database $db;

Show full diff (410 more lines)