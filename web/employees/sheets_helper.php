<?php
// Autoload a manuálisan telepített Google API Client-hez
require_once 'google-api-php-client-2.12.6/vendor/autoload.php';

/**
 * Google API kliens létrehozása és beállítása
 */
function getClient() {
    $client = new Google_Client();
    $client->setApplicationName('Mumi Google Sheets Integration');
    $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $client->setAuthConfig('credentials.json'); // A letöltött szolgáltatásfiók kulcs
    $client->setAccessType('offline');
    
    return $client;
}

/**
 * Adatok lekérése a Google Sheets dokumentumból
 */
function getSheetData($spreadsheetId, $range) {
    $client = getClient();
    $service = new Google_Service_Sheets($client);
    
    try {
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();
        
        if (empty($values)) {
            return [];
        }
        
        // Az első sort használjuk fejlécnek
        $header = array_shift($values);
        $data = [];
        
        foreach ($values as $row) {
            $rowData = [];
            foreach ($header as $index => $columnName) {
                $rowData[$columnName] = isset($row[$index]) ? $row[$index] : '';
            }
            $data[] = $rowData;
        }
        
        return $data;
    } catch (Exception $e) {
        error_log('Google Sheets API hiba: ' . $e->getMessage());
        return [];
    }
}

/**
 * Cégnevek lekérése a Google Sheets-ből
 */
function getCompanyNames($spreadsheetId) {
    try {
        $data = getSheetData($spreadsheetId, 'A:A'); // Csak a cég neveket tartalmazó oszlop
        
        // Egyedi cégnevek kinyerése
        $companies = [];
        foreach ($data as $row) {
            if (isset($row['NÉV']) && !empty($row['NÉV']) && !in_array($row['NÉV'], $companies)) {
                $companies[] = $row['NÉV'];
            }
        }
        
        return $companies;
    } catch (Exception $e) {
        error_log('Hiba a cégnevek lekérése közben: ' . $e->getMessage());
        return [];
    }
}

/**
 * Cég adatainak lekérése a Google Sheets-ből
 */
function getCompanyData($spreadsheetId, $companyName = null) {
    try {
        $data = getSheetData($spreadsheetId, 'A1:L100'); // Tartomány a táblázatban
        
        if ($companyName) {
            // Szűrés cég névre
            return array_filter($data, function($row) use ($companyName) {
                return isset($row['NÉV']) && $row['NÉV'] == $companyName;
            });
        }
        
        return $data;
    } catch (Exception $e) {
        error_log('Hiba a cégadatok lekérése közben: ' . $e->getMessage());
        return [];
    }
}

/**
 * Statisztikák számítása a cégadatokból
 */
function calculateStatistics($data) {
    $statistics = [
        'totalAds' => count($data),
        'totalAmount' => 0,
        'averageAmount' => 0
    ];
    
    foreach ($data as $row) {
        // Összegzés - a "Csomag értéke" oszlopból
        if (isset($row['Csomag értéke'])) {
            $amount = preg_replace('/[^0-9]/', '', $row['Csomag értéke']);
            $amount = intval($amount);
            $statistics['totalAmount'] += $amount;
        }
    }
    
    // Átlag számítás
    if ($statistics['totalAds'] > 0) {
        $statistics['averageAmount'] = $statistics['totalAmount'] / $statistics['totalAds'];
    }
    
    return $statistics;
}