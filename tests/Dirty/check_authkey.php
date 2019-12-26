<?php

require_once __DIR__.'/../../ClassLoader.php';

$authKeyHex = '51b862f93942693caf6695999d6c56694eb5540c69bf2353c18d999be1aabd506696cd6d0f10476dbe9e93a6cce0af7b997ede3b0686b3eacb8efdcdbf53e57b6fb00a2a8aa83f47abbdaeb17880ab6db1274805490f46621f9fc3a6c627976cf1f610cae9391c5e26b9eaf42e289061b77fa59596a405cacc90f8ad9277ec223c54f12aebbfb08caca5a28fc6b0c08eb7229f1750ecf6a14e35d1aae96ad83a90d9eebe1c975c4be5da9d63e471a6da851d5b59015a6dfd69f35cda57c0d907927cd8b74c96d85cf94a1048fca9a289a9c9e24281758840dbaed89068611388a207aee261b431e9236ec275150f6fb94bb4f46b43cbea99c35c1b552ed014c2';
$authKeyIdHex = '1f69c12bbeca0690';

$actualAuthKeyId = substr(sha1(hex2bin($authKeyHex), true), -8);
if(strcmp(hex2bin($authKeyIdHex), $actualAuthKeyId) == 0) {
    echo "AuthKey OK\n";
    echo base64_encode(hex2bin($authKeyHex));
}
