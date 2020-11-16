<?php

namespace app\components;

class VaillantAPI
{

    public $healthCheckPassed = false;
    public $lastGeneratedCommand;
    private $_ch;
    private $_lastCurlError;
    private $_lastCurlInfo;
    private $_lastCurlErrno;
    private $_proxyPort;
    private $_proxyHost;
    private $_apiAddress = 'https://smart.vaillant.com/mobile/api';
    private $_healthCheckAddress = 'https://smart.vaillant.com/mobile/';
    private $_apiVersion = 'v4';
    private $_apiType = 'live';
    private $_smartphoneId = 'multimaticweb';
    private $_username;
    private $_password;
    private $_authToken;
    private $_currentFacility;
    private $_loginRequired = false;
    private $_lastApiMeta;
    private $_enableCustomRequests;

    public function __construct($username, $password, $enableCustomRequests = false)
    {

        $this->_enableCustomRequests = $enableCustomRequests ? true : false;
        $this->_username             = $username;
        $this->_password             = $password;

        $this->_ch = curl_init();

        $cookies = \Yii::getAlias("@runtime") . "/" . $this->_username . ".txt";
        curl_setopt($this->_ch, CURLOPT_COOKIEJAR, $cookies);
        curl_setopt($this->_ch, CURLOPT_COOKIEFILE, $cookies);

        if (!empty($this->proxyHost)) {
            curl_setopt($this->_ch, CURLOPT_PROXY, $this->_proxyHost);
            curl_setopt($this->_ch, CURLOPT_PROXYPORT, $this->_proxyPort);
        }

        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);

        // Health check:
        if ($this->_makeCurlRequest('hs') && $this->_lastCurlInfo['http_code'] == 200) {
            $this->healthCheckPassed = true;
        }
    }

    private function _makeCurlRequest($command, $method = 'GET', $postFields = [], $httpheader = ['Content-Type: application/json'])
    {

        if ($command == 'hs') {
            curl_setopt($this->_ch, CURLOPT_URL, $this->_healthCheckAddress . '/');
        } else {
            $this->lastGeneratedCommand = $command;
            curl_setopt($this->_ch, CURLOPT_URL, $this->_apiAddress . '/' . $this->_apiVersion . '/' . $this->lastGeneratedCommand);
        }

        switch (strtolower($method)) {
            case 'post':
                curl_setopt($this->_ch, CURLOPT_POST, true);
                curl_setopt($this->_ch, CURLOPT_POSTFIELDS, json_encode($postFields));
                break;
            case 'head':
                curl_setopt($this->_ch, CURLOPT_NOBODY, true);
                break;
            default:
                curl_setopt($this->_ch, CURLOPT_HTTPGET, true);
                break;
        }

        curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $httpheader);

        curl_setopt($this->_ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($this->_ch, CURLOPT_TIMEOUT, 10); //timeout in seconds

        $result = curl_exec($this->_ch);

        $this->_lastCurlInfo = curl_getinfo($this->_ch);

        if (!$result) {
            $this->_lastCurlErrno = curl_errno($this->_ch);
            $this->_lastCurlError = curl_error($this->_ch);
            //$this->_lastCurlInfo = false;
            return false;
        }

        if ($this->_lastCurlInfo['http_code'] == 401 && !$this->_loginRequired) {
            \Yii::warning('The authentication is no longer valid!', 'VaillantAPI');
            $this->_loginRequired = true;
            if ($this->_login()) {
                // Make original request
                $return               = $this->_makeCurlRequest($command, $method, $postFields, $httpheader);
                $this->_loginRequired = false;
                if ($this->_lastCurlInfo['http_code'] != 401) {
                    return $return;
                } else {
                    return false;
                }
            }

            // Login failed.
            //$this->_lastCurlInfo = false;
            return false;
        }

        $json = json_decode($result);

        if (is_object($json)) {
            if (property_exists($json, 'meta')) {
                $this->_lastApiMeta = $json->meta;
            } else {
                $this->_lastApiMeta = false;
            }

            if (property_exists($json, 'body')) {
                return $json->body;
            } else {
                return $json;
            }
        }

        return $result;
    }

    private function _login()
    {

        $token_data = $this->_getAuthToken();

        if ($this->_lastCurlInfo['http_code'] != "200" || !is_object($token_data)) {
            return false;
        }
        $this->_authToken = $token_data->authToken;

        $this->_authenticate();

        if ($this->_lastCurlInfo['http_code'] != "200") {
            return false;
        }

        return true;

    }

    private function _getAuthToken()
    {
        return $this->_makeCurlRequest('account/authentication/v1/token/new', 'POST', [
            'smartphoneId' => $this->_smartphoneId,
            'username'     => $this->_username,
            'password'     => $this->_password
        ]);
    }

    private function _authenticate()
    {
        return $this->_makeCurlRequest('account/authentication/v1/authenticate', 'POST', [
            'smartphoneId' => $this->_smartphoneId,
            'username'     => $this->_username,
            'authToken'    => $this->_authToken
        ]);
    }

    public function getLastHttpCode()
    {
        return $this->_lastCurlInfo ? $this->_lastCurlInfo['http_code'] : $this->_lastCurlError['http_code'];
    }


    public function __destruct()
    {
        curl_close($this->_ch);
    }

    public function getApiMeta()
    {
        return $this->_lastApiMeta;
    }

    public function getFacilities()
    {

        $data = $this->_makeCurlRequest('facilities');
        return $data ? $data->facilitiesList : false;
    }

    public function setCurrentFacility($serialNumber)
    {
        $this->_currentFacility = $serialNumber;
    }

    /**
     * @return mixed The Box-Status (VR900)
     *
     * onlineStatus: ONLINE|OFFLINE
     * firmwareUpdateStatus: UPDATE_PENDING|UPDATE_NOT_PENDING
     * facilityInstallationStatus: ?
     */
    public function getBoxStatus()
    {
        return $this->_makeCurlRequest('facilities/' . $this->_currentFacility . '/system/v1/status');
    }

    /**
     * Return the name and current time for this facility:
     * facilityName: Name of this facility
     * facilityTime: time
     * facilityTimeZone: The time zone..
     *
     * @return mixed
     */
    public function getFacilityDetails()
    {
        return $this->_makeCurlRequest('facilities/' . $this->_currentFacility . '/system/v1/details');
    }

    /**
     * Return things like: is hot water (DHW = Domestic Hot Water) enabled or circulation pump available.
     * Seems to be always NOT_SET
     * @return bool|mixed|string
     */
    public function getFacilitySettings()
    {
        return $this->_makeCurlRequest('facilities/' . $this->_currentFacility . '/storage/default');
    }

    /**
     * Return installer email, phone and address/name
     * @return bool|mixed|string
     */
    public function getFacilityInstallerInfo()
    {
        return $this->_makeCurlRequest('facilities/' . $this->_currentFacility . '/system/v1/installerinfo');
    }

    /**
     * Return error information like maintenance or error
     * errorMessages: Array: title, type, statusCode etc..
     * @return bool|mixed|string
     */
    public function getDiagnosisState()
    {
        return $this->_makeCurlRequest('facilities/' . $this->_currentFacility . '/hvacstate/v1/overview');
    }

    /**
     * Return a long list of the system: zones, dhw, parameters, status etc.
     * @return bool|mixed|string
     */
    public function getSystemControl()
    {
        return $this->_makeCurlRequest('facilities/' . $this->_currentFacility . '/systemcontrol/v1');
    }

    /**
     * Returns outsidetemp and the time of this value:
     * datetime: The date and time
     * outside_temperature: in Celsius
     * @return bool|mixed|string
     */
    public function getSystemControlStatus()
    {
        return $this->_makeCurlRequest('facilities/' . $this->_currentFacility . '/systemcontrol/v1/status');
    }

    /**
     * Return zones with details.
     * @hint: setpoint: Solltemp, setback: Absenktemp.
     * @return bool|mixed|string
     */
    public function getZones()
    {
        return $this->_makeCurlRequest('facilities/' . $this->_currentFacility . '/systemcontrol/v1/zones');
    }

    /**
     * Gives current infos like current Flow temperatur, current hot water temp, current water pressure:
     * devices: Array: reports: ARRAY:
     *      unit: The unit (bar, Celsius, etc)
     *      value: The value
     *      _id: the device (like: DomesticHotWaterTankTemperature)
     * @return bool|mixed|string
     */
    public function getLiveReport()
    {
        return $this->_makeCurlRequest('facilities/' . $this->_currentFacility . '/livereport/v1');
    }

    public function getEEBusShipNode()
    {
        return $this->_makeCurlRequest('facilities/' . $this->_currentFacility . '/spine/v1/ship/self');
    }


    public function makeCustomRequest($command, $method)
    {
        if (!$this->_enableCustomRequests) {
            throw new \Exception("Custom requestst are not enabled!");
        }
        return $this->_makeCurlRequest(str_replace(["{serialNumber}"], [$this->_currentFacility], $command), $method);
    }

    public function getLastCurlError()
    {
        return $this->_lastCurlError;
    }

    public function getLastCurlInfo()
    {
        return $this->_lastCurlInfo;
    }


}
