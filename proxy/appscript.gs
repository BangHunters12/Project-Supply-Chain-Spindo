/**
 * Google Apps Script Proxy untuk AppSheet SIKUTA
 * PT SPINDO Tbk Unit 7 Gresik
 * 
 * === CARA DEPLOY ===
 * 1. Buka https://script.google.com
 * 2. New Project → Paste seluruh kode ini
 * 3. Ganti APP_ID dan ACCESS_KEY di bawah
 * 4. Deploy → New Deployment → Web App
 *    - Execute as: Me (akun Anda)
 *    - Who has access: Anyone
 * 5. Copy URL deployment → Set di .env sebagai APPSHEET_PROXY_URL
 *
 * === TESTING ===
 * - GET  URL → harusnya return { status: "ok", app_id: "..." }
 * - POST URL → kirim { tableName: "DATA Gudang", action: "Find" }
 */

// ═══════════════════════════════════════════════
// GANTI DENGAN CREDENTIALS ANDA
// ═══════════════════════════════════════════════
const APP_ID = '2841436f-c0cd-42c3-809b-f9e80fe52c00';
const ACCESS_KEY = 'PASTE_YOUR_ACCESS_KEY_HERE';

/**
 * Handle GET requests — test koneksi
 */
function doGet(e) {
  return ContentService.createTextOutput(JSON.stringify({
    status: 'ok',
    app_id: APP_ID,
    timestamp: new Date().toISOString(),
    message: 'SIKUTA AppSheet Proxy is running'
  })).setMimeType(ContentService.MimeType.JSON);
}

/**
 * Handle POST requests — forward ke AppSheet API
 */
function doPost(e) {
  try {
    const request = JSON.parse(e.postData.contents);
    const tableName = request.tableName;
    const action = request.action || 'Find';
    const filters = request.filters || [];

    if (!tableName) {
      return jsonResponse({ error: 'tableName is required' }, 400);
    }

    const apiUrl = `https://api.appsheet.com/api/v2/apps/${APP_ID}/tables/${encodeURIComponent(tableName)}/Action`;

    const payload = {
      Action: action,
      Properties: {
        Locale: 'id-ID',
        Timezone: 'Asia/Jakarta'
      },
      Rows: filters
    };

    const response = UrlFetchApp.fetch(apiUrl, {
      method: 'POST',
      headers: {
        'ApplicationAccessKey': ACCESS_KEY,
        'Content-Type': 'application/json'
      },
      payload: JSON.stringify(payload),
      muteHttpExceptions: true
    });

    const statusCode = response.getResponseCode();
    const responseText = response.getContentText();

    if (statusCode === 200) {
      return ContentService.createTextOutput(responseText)
        .setMimeType(ContentService.MimeType.JSON);
    } else {
      return jsonResponse({
        error: 'AppSheet API Error',
        status: statusCode,
        message: responseText
      });
    }

  } catch (error) {
    return jsonResponse({
      error: 'Proxy Error',
      message: error.message
    });
  }
}

function jsonResponse(data, status) {
  return ContentService.createTextOutput(JSON.stringify(data))
    .setMimeType(ContentService.MimeType.JSON);
}
