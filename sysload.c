#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "zend_exceptions.h"
#include "zend_smart_str.h"
#include <openssl/evp.h>
#include <string.h>

/**
 * [!] By Colton Silva (chinawaterstealers).
 * sysload.so - SilvaSystems Secure Native Loader
 */

#define SYS_SIGNATURE "SECURITYBYSILVASYSTEMS"
#define SYS_KEY "silvasystemschinawaterstealers01"
#define SYS_IV "chinawatersteale"
#define SYS_ALLOWED_DIR "/home/pi/orange-wifi/admin/"

PHP_FUNCTION(sysload_run) {
    char *path, *secure_data = NULL;
    size_t path_len, secure_data_len = 0;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "s|s", &path, &path_len, &secure_data, &secure_data_len) == FAILURE) {
        return;
    }

    // 1. Path Enforcement
    char *real_p = realpath(path, NULL);
    if (!real_p || strncmp(real_p, SYS_ALLOWED_DIR, strlen(SYS_ALLOWED_DIR)) != 0) {
        if (real_p) free(real_p);
        zend_throw_exception(NULL, "[!] Access Denied: Path restriction violation.", 0);
        return;
    }

    // 2. Read File
    FILE *f = fopen(real_p, "rb");
    free(real_p);
    if (!f) {
        zend_throw_exception(NULL, "[!] Error: Logic segment missing.", 0);
        return;
    }

    fseek(f, 0, SEEK_END);
    long fsize = ftell(f);
    fseek(f, 0, SEEK_SET);

    if (fsize <= (long)strlen(SYS_SIGNATURE)) {
        fclose(f);
        zend_throw_exception(NULL, "[!] Error: Invalid segment size.", 0);
        return;
    }

    unsigned char *content = emalloc(fsize);
    fread(content, 1, fsize, f);
    fclose(f);

    // 3. Signature Check
    if (memcmp(content, SYS_SIGNATURE, strlen(SYS_SIGNATURE)) != 0) {
        efree(content);
        zend_throw_exception(NULL, "[!] Error: Invalid file signature.", 0);
        return;
    }

    // 4. Decrypt
    int ciphertext_len = fsize - strlen(SYS_SIGNATURE);
    unsigned char *ciphertext = content + strlen(SYS_SIGNATURE);
    unsigned char *plaintext = emalloc(ciphertext_len + 128);
    int plaintext_len;

    EVP_CIPHER_CTX *ctx = EVP_CIPHER_CTX_new();
    EVP_DecryptInit_ex(ctx, EVP_aes_256_cbc(), NULL, (unsigned char*)SYS_KEY, (unsigned char*)SYS_IV);
    
    int len;
    EVP_DecryptUpdate(ctx, plaintext, &len, ciphertext, ciphertext_len);
    plaintext_len = len;
    
    if (EVP_DecryptFinal_ex(ctx, plaintext + len, &len) <= 0) {
        EVP_CIPHER_CTX_free(ctx);
        efree(content);
        efree(plaintext);
        zend_throw_exception(NULL, "[!] Error: Integrity check failed.", 0);
        return;
    }
    plaintext_len += len;
    plaintext[plaintext_len] = '\0';

    EVP_CIPHER_CTX_free(ctx);
    efree(content);

    // 5. Execution
    smart_str exec_code = {0};
    if (secure_data) {
        smart_str_appends(&exec_code, "<?php $SECURE_DATA = '");
        smart_str_appends(&exec_code, secure_data);
        smart_str_appends(&exec_code, "'; ?>");
    }
    smart_str_appends(&exec_code, (char*)plaintext);
    smart_str_0(&exec_code);

    zend_eval_string(ZSTR_VAL(exec_code.s), NULL, "sysload_exec");

    smart_str_free(&exec_code);
    efree(plaintext);
}

static const zend_function_entry sysload_functions[] = {
    PHP_FE(sysload_run, NULL)
    PHP_FE_END
};

zend_module_entry sysload_module_entry = {
    STANDARD_MODULE_HEADER,
    "sysload",
    sysload_functions,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    "1.1",
    STANDARD_MODULE_PROPERTIES
};

ZEND_GET_MODULE(sysload)
