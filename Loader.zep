namespace Sysload;

/**
 * [!] By Colton Silva (chinawaterstealers).
 * SilvaSystems Secure Native Loader (sysload.so)
 */
class Loader
{
    /**
     * Secret AES Key (32 bytes)
     */
    private static key = "silvasystemschinawaterstealers01";

    /**
     * Secret AES IV (16 bytes)
     */
    private static iv = "chinawatersteale";

    /**
     * Valid Signature
     */
    private static signature = "SECURITYBYSILVASYSTEMS";

    /**
     * Allowed base directory
     */
    private static allowedDir = "/home/pi/orange-wifi/admin/";

    /**
     * Securely run encrypted logic
     */
    public static function run(string path, string secureData = "")
    {
        var realPath, content, fileSig, encryptedData, decryptedCode, injection;

        // 1. Path Enforcement
        let realPath = realpath(path);
        if !realPath || strpos(realPath, self::allowedDir) !== 0 {
            throw new \Exception("[!] Access Denied: Secure logic must reside in " . self::allowedDir);
        }

        if !file_exists(realPath) {
            throw new \Exception("[!] Error: Logic segment missing.");
        }

        // 2. Read File
        let content = file_get_contents(realPath);
        if !content {
            throw new \Exception("[!] Error: Failed to read logic segment.");
        }

        // 3. Signature Check
        let fileSig = substr(content, 0, strlen(self::signature));
        if fileSig !== self::signature {
            throw new \Exception("[!] Error: Invalid file signature.");
        }

        // 4. Decrypt
        let encryptedData = substr(content, strlen(self::signature));
        let decryptedCode = openssl_decrypt(
            encryptedData,
            "aes-256-cbc",
            self::key,
            OPENSSL_RAW_DATA,
            self::iv
        );

        if decryptedCode === false {
            throw new \Exception("[!] Error: Integrity check failed. Logic compromised.");
        }

        // 5. Native Execution
        // We inject the variable directly into the execution context
        if secureData !== "" {
            let injection = "<?php $SECURE_DATA = '" . secureData . "'; ?>";
            eval("?> " . injection . decryptedCode);
        } else {
            eval("?> " . decryptedCode);
        }
    }
}