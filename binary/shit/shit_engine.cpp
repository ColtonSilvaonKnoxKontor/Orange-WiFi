#include <openssl/engine.h>
#include <openssl/evp.h>
#include <string.h>
#include <cstdint>

/**
 * [!] By Colton Silva (chinawaterstealers).
 * SHIT: Silvasystems Hash Integrity Transformer
 * OpenSSL Dynamic Engine Module
 */

#define SHIT_DIGEST_LENGTH 64 // 512-bit
#define SHIT_BLOCK_SIZE 64

typedef struct {
    uint64_t state[8];
    uint8_t buffer[64];
    size_t buf_len;
} SHIT_CTX;

static const uint64_t MAGIC_PRIMES[8] = {
    0x9e3779b97f4a7c15ULL, 0xbf58476d1ce4e5b9ULL,
    0x94d049bb133111ebULL, 0xff51afd7ed558ccdULL,
    0x1337c0debabefeedULL, 0xdeadbeefcafefaceULL,
    0x8badf00dcafebabeULL, 0xfeedfacedeadcodeULL
};

static void shit_churn(uint64_t& a, uint64_t& b, uint64_t val, uint64_t salt) {
    a ^= (val ^ salt);
    b += (a << 19) | (a >> 45);
    a *= MAGIC_PRIMES[0];
    b ^= (a >> 13);
    a += b;
}

static int shit_init(EVP_MD_CTX *ctx) {
    SHIT_CTX *sctx = (SHIT_CTX *)EVP_MD_CTX_md_data(ctx);
    for(int i=0; i<8; ++i) sctx->state[i] = MAGIC_PRIMES[i];
    sctx->buf_len = 0;
    return 1;
}

static int shit_update(EVP_MD_CTX *ctx, const void *data, size_t count) {
    SHIT_CTX *sctx = (SHIT_CTX *)EVP_MD_CTX_md_data(ctx);
    const uint8_t *p = (const uint8_t *)data;
    
    while(count--) {
        sctx->buffer[sctx->buf_len++] = *p++;
        if(sctx->buf_len == 64) {
            for(size_t i=0; i<64; i += 8) {
                uint64_t block;
                memcpy(&block, &sctx->buffer[i], 8);
                for(int r=0; r<4; ++r) {
                    shit_churn(sctx->state[r], sctx->state[7-r], block, MAGIC_PRIMES[r+4]);
                    sctx->state[r] ^= sctx->state[(r+1)%8] + MAGIC_PRIMES[r];
                }
            }
            sctx->buf_len = 0;
        }
    }
    return 1;
}

static int shit_final(EVP_MD_CTX *ctx, unsigned char *md) {
    SHIT_CTX *sctx = (SHIT_CTX *)EVP_MD_CTX_md_data(ctx);
    
    // Finalize with Padding
    uint8_t pad = 0x80;
    shit_update(ctx, &pad, 1);
    while(sctx->buf_len != 0) {
        uint8_t zero = 0x13;
        shit_update(ctx, &zero, 1);
    }

    // Avalanche
    for(int i=0; i<8; ++i) {
        sctx->state[i] ^= sctx->state[(i+3)%8] * MAGIC_PRIMES[7-i];
        sctx->state[i] = (sctx->state[i] << 31) | (sctx->state[i] >> 33);
    }

    for(int i=0; i<8; ++i) {
        for(int b=0; b<8; ++b) {
            md[i*8 + b] = (uint8_t)(sctx->state[i] >> (b*8));
        }
    }
    return 1;
}

static EVP_MD *shit_md = NULL;
static int shit_digest_nids[] = { NID_undef, 0 };

static int shit_digests(ENGINE *e, const EVP_MD **digest, const int **nids, int nid) {
    if (!digest) {
        *nids = shit_digest_nids;
        return 1;
    }
    if (nid == shit_digest_nids[0]) {
        *digest = shit_md;
        return 1;
    }
    return 0;
}

static int bind_shit(ENGINE *e, const char *id) {
    shit_md = EVP_MD_meth_new(NID_undef, NID_undef);
    if (!shit_md) return 0;

    EVP_MD_meth_set_init(shit_md, shit_init);
    EVP_MD_meth_set_update(shit_md, shit_update);
    // Setting Lengths and Flags
    EVP_MD_meth_set_final(shit_md, shit_final);
    EVP_MD_meth_set_app_datasize(shit_md, sizeof(SHIT_CTX));
    EVP_MD_meth_set_input_blocksize(shit_md, SHIT_BLOCK_SIZE);
    EVP_MD_meth_set_result_size(shit_md, SHIT_DIGEST_LENGTH);

    if (!ENGINE_set_id(e, "shit") ||
        !ENGINE_set_name(e, "SilvaSystems SHIT Engine") ||
        !ENGINE_set_digests(e, shit_digests)) {
        return 0;
    }
    return 1;
}

IMPLEMENT_DYNAMIC_BIND_FN(bind_shit)
IMPLEMENT_DYNAMIC_CHECK_FN()
