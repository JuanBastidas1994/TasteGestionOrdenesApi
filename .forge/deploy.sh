# PHP 8.4. Sin Composer: no hay composer.json en la raíz.

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY

if [ ! -f .env ]; then
    echo "ERROR: falta .env. Pegalo en Forge → Environment."
    exit 1
fi

SHARED="$FORGE_SITE_ROOT/shared"
share() {
    local rel="$1"
    mkdir -p "$SHARED/$rel"
    mkdir -p "$(dirname "$rel")"
    rm -rf "$rel"
    ln -sfn "$SHARED/$rel" "$rel"
}

share logs
share cache

chmod -R ug+rwx logs cache 2>/dev/null || true

$ACTIVATE_RELEASE()
