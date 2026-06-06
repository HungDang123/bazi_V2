<?php

namespace App\Services;

use Exception;
use DOMDocument;
use DOMXPath;
use DateTime;
use DateTimeZone;


class JoeyYapCrawlerService
{
    /** Thời gian chờ kết nối tối đa (giây). */
    protected const CONNECT_TIMEOUT = 5;

    /** Thời gian chờ tối đa cho mỗi request (giây). */
    protected const REQUEST_TIMEOUT = 15;

    protected $username;
    protected $password;

    /**
     * Constructor
     */
    public function __construct($username = null, $password = null)
    {
        $this->username = $username ?? 'truong.xdat@gmail.com';
        $this->password = $password ?? '@Vanpersie12345';
    }

    /**
     * Query profiles strength chart data
     *
     * @param int $day
     * @param int $month
     * @param int $year
     * @param int|null $hour
     * @param int $min
     * @param bool $isTimeOfBirthUnknown
     * @param int $gender (0: Male, 1: Female)
     * @return array
     */
    public function queryProfilesStrengthChart($day, $month, $year, $hour = null, $min = 0, $isTimeOfBirthUnknown = false, $gender = 0)
    {
        try {
            if ($hour === null) {
                $hour = 0;
            }

            $content = $this->queryProfilesStrength(
                intval($day),
                intval($month),
                intval($year),
                intval($hour),
                intval($min),
                $isTimeOfBirthUnknown,
                $gender
            );

            $data = $this->parseData($content);
            
            return $data;
        } catch (Exception $e) {
            throw new Exception("Failed to query profiles strength chart: " . $e->getMessage());
        }
    }

    /**
     * Query profiles strength from Joey Yap Bazi website
     *
     * @param int $day
     * @param int $month
     * @param int $year
     * @param int $hour
     * @param int $min
     * @param bool $isTimeOfBirthUnknown
     * @param int $gender
     * @return string
     * @throws Exception
     */
    protected function queryProfilesStrength($day, $month, $year, $hour, $min, $isTimeOfBirthUnknown, $gender)
    {
        $siteGender = $this->mapGenderToBazi($gender);
        $cookieFile = storage_path('app/temp/bazi_cookie_' . uniqid() . '.txt');

        // Ensure directory exists
        if (!is_dir(dirname($cookieFile))) {
            mkdir(dirname($cookieFile), 0755, true);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_ENCODING       => '',
            CURLOPT_COOKIEJAR      => $cookieFile,
            CURLOPT_COOKIEFILE     => $cookieFile,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT        => self::REQUEST_TIMEOUT,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER     => [
                'Accept-Language: en-US,en;q=0.9',
            ]
        ]);

        try {
            // Step 1: Try to login (skip if not needed)
            try {
                $this->login($ch);
            } catch (Exception $e) {
                \Log::warning('Login skipped or failed: ' . $e->getMessage());
                // Continue without login - website may not require it
            }

            // Step 2: GET the Plot page with proper headers
            // Reset POST settings and switch to GET (giữ nguyên timeout đã set ở curl_init)
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://bazi.joeyyap.com/Plot',
                CURLOPT_HTTPGET => true,
                CURLOPT_POST => false,
                CURLOPT_POSTFIELDS => null,
                CURLOPT_CUSTOMREQUEST => null,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Connection: keep-alive',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.9',
                    'Cache-Control: max-age=0',
                    'Upgrade-Insecure-Requests: 1',
                    'Referer: https://bazi.joeyyap.com/Default',
                ]
            ]);

            $getHtml = curl_exec($ch);

            if ($getHtml === false) {
                throw new Exception('Failed to fetch Plot page: ' . curl_error($ch));
            }

            $finalPlotUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

            // Extract hidden fields
            $viewstate = $this->extractHiddenField($getHtml, '__VIEWSTATE');
            $viewstateGenerator = $this->extractHiddenField($getHtml, '__VIEWSTATEGENERATOR');
            $eventValidation = $this->extractHiddenField($getHtml, '__EVENTVALIDATION');

            if (!$viewstate || !$viewstateGenerator || !$eventValidation) {
                throw new Exception('Failed to extract hidden fields from Plot page. Final URL: ' . $finalPlotUrl);
            }

            // Build hidden field time
            $now = new DateTime('now', new DateTimeZone('Asia/Bangkok'));
            $hiddenFieldTimeFinal =
                $now->format('Y') . '|' .
                (int)$now->format('n') . '|' .
                (int)$now->format('j') . '|' .
                (int)$now->format('G') . '|' .
                (int)$now->format('i') . '|' .
                (int)$now->format('s');

            $name = 'Quang Hung';

            // Step 3: AJAX - Change Year (để lấy VIEWSTATE mới)
            $formYear = [
                'ctl00$ctl10' => 'ctl00$MainContent$ctl00|ctl00$MainContent$cbxYear',
                'ctl00$MainContent$HiddenFieldTimeFinal' => $hiddenFieldTimeFinal,
                'ctl00$MainContent$txtName' => $name,
                'ctl00$MainContent$ddlGender' => (string)$siteGender,
                'ctl00$MainContent$cbxYear' => (string)$year,
                'ctl00$MainContent$cbxMonth' => $now->format('n'),
                'ctl00$MainContent$cbxDay' => $now->format('j'),
                'ctl00$MainContent$ddlHour' => (string)$hour,
                'ctl00$MainContent$ddlMin' => (string)$min,
                '__LASTFOCUS' => '',
                '__EVENTTARGET' => 'ctl00$MainContent$cbxYear',
                '__EVENTARGUMENT' => '',
                '__VIEWSTATE' => $viewstate,
                '__VIEWSTATEGENERATOR' => $viewstateGenerator,
                '__EVENTVALIDATION' => $eventValidation,
                '__ASYNCPOST' => 'true',
            ];
            if ($isTimeOfBirthUnknown) {
                $formYear['ctl00$MainContent$chkUnknown'] = 'on';
            }

            $responseYear = $this->postAjax($ch, $formYear);

            // Parse VIEWSTATE mới từ response
            $viewstate = $this->extractViewStateFromAjax($responseYear, '__VIEWSTATE') ?: $viewstate;
            $eventValidation = $this->extractViewStateFromAjax($responseYear, '__EVENTVALIDATION') ?: $eventValidation;

            // Step 4: AJAX - Change Month (để lấy VIEWSTATE với Day list hợp lệ)
            $formMonth = [
                'ctl00$ctl10' => 'ctl00$MainContent$ctl00|ctl00$MainContent$cbxMonth',
                'ctl00$MainContent$HiddenFieldTimeFinal' => $hiddenFieldTimeFinal,
                'ctl00$MainContent$txtName' => $name,
                'ctl00$MainContent$ddlGender' => (string)$siteGender,
                'ctl00$MainContent$cbxYear' => (string)$year,
                'ctl00$MainContent$cbxMonth' => (string)$month,
                'ctl00$MainContent$cbxDay' => '1',
                'ctl00$MainContent$ddlHour' => (string)$hour,
                'ctl00$MainContent$ddlMin' => (string)$min,
                '__LASTFOCUS' => '',
                '__EVENTTARGET' => 'ctl00$MainContent$cbxMonth',
                '__EVENTARGUMENT' => '',
                '__VIEWSTATE' => $viewstate,
                '__VIEWSTATEGENERATOR' => $viewstateGenerator,
                '__EVENTVALIDATION' => $eventValidation,
                '__ASYNCPOST' => 'true',
            ];

            if ($isTimeOfBirthUnknown) {
                $formMonth['ctl00$MainContent$chkUnknown'] = 'on';
            }

            $responseMonth = $this->postAjax($ch, $formMonth);

            // Parse VIEWSTATE mới từ response
            $viewstate = $this->extractViewStateFromAjax($responseMonth, '__VIEWSTATE') ?: $viewstate;
            $eventValidation = $this->extractViewStateFromAjax($responseMonth, '__EVENTVALIDATION') ?: $eventValidation;

            // Step 5: Submit form với VIEWSTATE cuối cùng
            $formSubmit = [
                'ctl00$ctl10' => 'ctl00$MainContent$ctl00|ctl00$MainContent$btnSubmit',
                'ctl00$MainContent$HiddenFieldTimeFinal' => $hiddenFieldTimeFinal,
                'ctl00$MainContent$txtName' => $name,
                'ctl00$MainContent$ddlGender' => (string)$siteGender,
                'ctl00$MainContent$cbxYear' => (string)$year,
                'ctl00$MainContent$cbxMonth' => (string)$month,
                'ctl00$MainContent$cbxDay' => (string)$day,
                'ctl00$MainContent$ddlHour' => (string)$hour,
                'ctl00$MainContent$ddlMin' => (string)$min,
                '__LASTFOCUS' => '',
                '__EVENTTARGET' => '',
                '__EVENTARGUMENT' => '',
                '__VIEWSTATE' => $viewstate,
                '__VIEWSTATEGENERATOR' => $viewstateGenerator,
                '__EVENTVALIDATION' => $eventValidation,
                '__ASYNCPOST' => 'true',
                'ctl00$MainContent$btnSubmit' => 'Plot',
            ];

            if ($isTimeOfBirthUnknown) {
                $formSubmit['ctl00$MainContent$chkUnknown'] = 'on';
            }

            $postResult = $this->postAjax($ch, $formSubmit);

            // Extract redirect URL
            if (!preg_match('/pageRedirect\|\|(.+?)\|/', $postResult, $m)) {
                throw new Exception('Redirect URL not found in response');
            }

            $redirectUrl = urldecode($m[1]);

            // Step 6: Follow redirect to get final result
            curl_setopt_array($ch, [
                CURLOPT_URL            => 'https://bazi.joeyyap.com' . $redirectUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING       => '',
                CURLOPT_HTTPGET        => true,
                CURLOPT_HTTPHEADER     => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.9',
                ],
            ]);

            $finalHtml = curl_exec($ch);

            if ($finalHtml === false) {
                throw new Exception('Failed to fetch final result: ' . curl_error($ch));
            }

            return $finalHtml;
        } finally {
            if ($ch instanceof \CurlHandle || is_resource($ch)) {
                curl_close($ch);
            }
            @unlink($cookieFile);
        }
    }

    /**
     * Login to Joey Yap Bazi website
     *
     * @param resource $ch
     * @return void
     * @throws Exception
     */
    protected function login($ch)
    {
        if (!$this->username || !$this->password) {
            throw new Exception('Bazi credentials not configured.');
        }

        // Step 1: GET root page first to establish session
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://bazi.joeyyap.com/',
            CURLOPT_HTTPGET => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Connection: keep-alive',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.9',
                'Cache-Control: max-age=0',
                'Upgrade-Insecure-Requests: 1',
            ]
        ]);

        $homePage = curl_exec($ch);

        if ($homePage === false) {
            throw new Exception('Failed to fetch home page: ' . curl_error($ch));
        }

        $homeUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        \Log::info('Home page URL after redirects: ' . $homeUrl);

        // Check if there's a login form on the page
        if (stripos($homePage, 'txtUsername') === false && stripos($homePage, 'txtEmail') === false) {
            \Log::info('No login form found on home page - website may not require login');
            return; // No login needed
        }

        // Extract hidden fields from home/login page
        $viewstate = $this->extractHiddenField($homePage, '__VIEWSTATE');
        $viewstateGenerator = $this->extractHiddenField($homePage, '__VIEWSTATEGENERATOR');
        $eventValidation = $this->extractHiddenField($homePage, '__EVENTVALIDATION');
        $hdnDay = $this->extractHiddenField($homePage, 'ctl00$MainContent$hdnDay');

        \Log::info('Extracted fields from home page', [
            'viewstate' => $viewstate ? 'Found (' . strlen($viewstate) . ' chars)' : 'Not found',
            'viewstateGenerator' => $viewstateGenerator ?? 'Not found',
            'eventValidation' => $eventValidation ? 'Found (' . strlen($eventValidation) . ' chars)' : 'Not found',
            'hdnDay' => $hdnDay ?? 'Not found',
        ]);

        if (!$viewstate || !$viewstateGenerator || !$eventValidation) {
            throw new Exception('Failed to extract hidden fields from login page. URL: ' . $homeUrl);
        }

        \Log::info('Home page hidden fields extracted successfully');

        // Step 2: POST login credentials to the same URL
        $loginForm = [
            'ctl00$MainContent$txtUsername' => $this->username,
            'ctl00$MainContent$txtPassword' => $this->password,
            'ctl00$MainContent$credential' => '',
            'ctl00$MainContent$loginType' => '',
            'ctl00$MainContent$hdnDay' => $hdnDay ?? '',
            'ctl00$MainContent$btnLogin' => 'Login',
            '__EVENTTARGET' => '',
            '__EVENTARGUMENT' => '',
            '__VIEWSTATE' => $viewstate,
            '__VIEWSTATEGENERATOR' => $viewstateGenerator,
            '__EVENTVALIDATION' => $eventValidation,
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $homeUrl, // Use the actual URL we got
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($loginForm),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Connection: keep-alive',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept-Language: en-US,en;q=0.9',
                'Cache-Control: max-age=0',
                'Upgrade-Insecure-Requests: 1',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Referer: ' . $homeUrl,
            ]
        ]);

        $loginResult = curl_exec($ch);

        if ($loginResult === false) {
            throw new Exception('Login request failed: ' . curl_error($ch));
        }

        // Check if login was successful
        if (stripos($loginResult, 'incorrect') !== false || stripos($loginResult, 'invalid') !== false) {
            throw new Exception('Login failed: Invalid credentials');
        }

        // Check if redirected (successful login usually redirects)
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode == 302 || $httpCode == 301) {
            \Log::info('Login successful - got redirect (HTTP ' . $httpCode . ')');
        } else {
            \Log::info('Login response received (HTTP ' . $httpCode . ')');
        }

        \Log::info('Successfully logged in to Bazi website');
    }

    /**
     * POST AJAX request
     *
     * @param resource $ch
     * @param array $form
     * @return string
     * @throws Exception
     */
    protected function postAjax($ch, $form)
    {
        curl_setopt_array($ch, [
            CURLOPT_URL        => 'https://bazi.joeyyap.com/Plot',
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => http_build_query($form),
            CURLOPT_HTTPHEADER => [
                'X-Requested-With: XMLHttpRequest',
                'X-MicrosoftAjax: Delta=true',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Referer: https://bazi.joeyyap.com/Plot',
                'Accept: */*',
            ]
        ]);

        $result = curl_exec($ch);

        if ($result === false) {
            throw new Exception('AJAX request failed: ' . curl_error($ch));
        }

        return $result;
    }

    /**
     * Extract hidden field from HTML
     *
     * @param string $html
     * @param string $fieldName
     * @return string|null
     */
    protected function extractHiddenField($html, $fieldName)
    {
        // Try pattern with both name and id
        $pattern = '/<input type="hidden" name="' . preg_quote($fieldName, '/') . '" id="' . preg_quote($fieldName, '/') . '" value="([^"]+)"/i';
        if (preg_match($pattern, $html, $matches)) {
            return $matches[1];
        }

        // Try pattern with only name (more flexible)
        $pattern = '/<input type="hidden" name="' . preg_quote($fieldName, '/') . '" value="([^"]+)"/i';
        if (preg_match($pattern, $html, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract VIEWSTATE from AJAX response
     *
     * @param string $response
     * @param string $fieldName
     * @return string|null
     */
    protected function extractViewStateFromAjax($response, $fieldName)
    {
        // AJAX response format: fieldLength|hiddenField|__VIEWSTATE|value|...
        $pattern = '/\|' . preg_quote($fieldName, '/') . '\|([^|]+)\|/';
        if (preg_match($pattern, $response, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Parse HTML content and extract data
     *
     * @param string $content
     * @return array
     * @throws Exception
     */
    protected function parseData($content)
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        @$dom->loadHTML($content);

        // Extract five elements chart data
        preg_match_all('/<script>fnsFiveStruture\((.+),(.+),(.+),(.+),(.+)\)<\/script><script>/', $content, $fnsFiveStruture);
        preg_match_all('/<script>fnsFiveStruturesub\((.+),(.+),(.+),(.+),(.+)\)<\/script>/', $content, $fnsFiveStruturesub);

        if (empty($fnsFiveStruture[1]) || empty($fnsFiveStruturesub[1])) {
            throw new Exception('3RF1L');
        }

        $fiveStructures = [
            'natal_a' => $fnsFiveStruture[1][0],
            'natal_b' => $fnsFiveStruture[2][0],
            'natal_c' => $fnsFiveStruture[3][0],
            'natal_d' => $fnsFiveStruture[4][0],
            'natal_e' => $fnsFiveStruture[5][0],
            'annual_a' => $fnsFiveStruturesub[1][0],
            'annual_b' => $fnsFiveStruturesub[2][0],
            'annual_c' => $fnsFiveStruturesub[3][0],
            'annual_d' => $fnsFiveStruturesub[4][0],
            'annual_e' => $fnsFiveStruturesub[5][0],
        ];

        // Extract five elements labels
        $lblWealth = $dom->getElementById('MainContent_lblWealth');
        $lblResource = $dom->getElementById('MainContent_lblResource');
        $lblOutput = $dom->getElementById('MainContent_lblOutput');
        $lblInfluence = $dom->getElementById('MainContent_lblInfluence');
        $lblCompanion = $dom->getElementById('MainContent_lblCompanion');

        $fiveStructures['lblWealth'] = $lblWealth ? $this->getVNFiveElementText($lblWealth->textContent) : '';
        $fiveStructures['lblResource'] = $lblResource ? $this->getVNFiveElementText($lblResource->textContent) : '';
        $fiveStructures['lblOutput'] = $lblOutput ? $this->getVNFiveElementText($lblOutput->textContent) : '';
        $fiveStructures['lblInfluence'] = $lblInfluence ? $this->getVNFiveElementText($lblInfluence->textContent) : '';
        $fiveStructures['lblCompanion'] = $lblCompanion ? $this->getVNFiveElementText($lblCompanion->textContent) : '';

        // Extract Ten Profile data
        $xpath = new DOMXPath($dom);
        $trs = $xpath->query("//table[contains(@class,'TenProfile')]//tr");

        $strengthData = [
            'data' => []
        ];

        if ($trs->length > 1) {
            for ($i = 1; $i < $trs->length; $i++) {
                $tdRows = $trs[$i]->getElementsByTagName('td');
                if ($tdRows->length > 1) {
                    $spans = $tdRows[1]->getElementsByTagName('span');
                    if ($spans->length > 0) {
                        $enChartName = $spans[0]->textContent;
                        $vnChartName = $this->getVNChartName($enChartName);

                        $rawNatal = $dom->getElementById('MainContent_rptProfiles_Label4_' . ($i - 1));
                        $natal = $rawNatal ? str_replace('%', '', $rawNatal->textContent) : '0';

                        $rawAnnual = $dom->getElementById('MainContent_rptProfiles_Label3_' . ($i - 1));
                        $annual = $rawAnnual ? str_replace('%', '', $rawAnnual->textContent) : '0';

                        $strengthData['data'][] = [
                            'name' => $vnChartName,
                            'natal' => $natal,
                            'annual' => $annual,
                        ];
                    }
                }
            }
        }

        return [
            'five_structures' => $fiveStructures,
            'strength_data' => $strengthData,
        ];
    }

    /**
     * Map gender to Bazi site format
     *
     * @param int $gender (0: Male, 1: Female)
     * @return int (1: Male, -1: Female)
     */
    protected function mapGenderToBazi($gender)
    {
        return $gender == 0 ? 1 : -1;
    }

    /**
     * Get Vietnamese chart name from English name
     *
     * @param string $engName
     * @return string
     */
    protected function getVNChartName($engName)
    {
        $mapping = [
            'Direct Officer' => 'Chính Quan',
            'Friend' => 'Tỷ Kiên',
            'Indirect Resource' => 'Thiên Ấn',
            'Direct Wealth' => 'Chính Tài',
            'Eating God' => 'Thực Thần',
            'Hurting Officer' => 'Thương Quan',
            'Indirect Wealth' => 'Thiên Tài',
            'Rob Wealth' => 'Kiếp Tài',
            'Seven Killings' => 'Thất Sát',
            'Direct Resource' => 'Chính Ấn',
        ];

        foreach ($mapping as $eng => $vn) {
            if (strpos($engName, $eng) !== false) {
                return $vn;
            }
        }

        return '';
    }

    /**
     * Get Vietnamese five element text from English
     *
     * @param string $engElement
     * @return string
     */
    protected function getVNFiveElementText($engElement)
    {
        $engElement = strtoupper($engElement);

        $mapping = [
            'WOOD' => 'Mộc',
            'FIRE' => 'Hỏa',
            'EARTH' => 'Thổ',
            'METAL' => 'Kim',
            'WATER' => 'Thủy',
        ];

        foreach ($mapping as $eng => $vn) {
            if (strpos($engElement, $eng) !== false) {
                return $vn;
            }
        }

        return '';
    }
}