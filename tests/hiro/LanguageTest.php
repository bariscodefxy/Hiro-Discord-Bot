<?php

declare(strict_types=1);

namespace hiro\tests;

use hiro\parts\Language;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class LanguageTest extends TestCase
{
    private string $translationsDir;

    protected function setUp(): void
    {
        $this->translationsDir = dirname(__DIR__, 2) . '/translations';
    }

    public function testLoadsSupportedLocales(): void
    {
        $en = new Language('en_EN');
        $tr = new Language('tr_TR');
        $ja = new Language('ja_JP');
        $kr = new Language('kr_KR');

        $this->assertNotSame('commands.ping.reply', $en->getTranslator()->trans('commands.ping.reply'));
        $this->assertNotSame('commands.ping.reply', $tr->getTranslator()->trans('commands.ping.reply'));
        $this->assertNotSame('commands.ping.reply', $ja->getTranslator()->trans('commands.ping.reply'));
        $this->assertNotSame('global.user_not_found', $kr->getTranslator()->trans('global.user_not_found'));
    }

    public function testFallsBackToEnglishForUnknownLocaleOrPartialTranslation(): void
    {
        $en = new Language('en_EN');
        $unknown = new Language('fr_FR');
        $kr = new Language('kr_KR');

        $expectedPingReply = $en->getTranslator()->trans('commands.ping.reply');

        $this->assertSame($expectedPingReply, $unknown->getTranslator()->trans('commands.ping.reply'));
        $this->assertSame($expectedPingReply, $kr->getTranslator()->trans('commands.ping.reply'));
    }

    public function testFullKeyParityBetweenEnglishTurkishAndJapanese(): void
    {
        $enKeys = $this->flattenKeys(Yaml::parseFile($this->translationsDir . '/en_EN.yaml'));
        $trKeys = $this->flattenKeys(Yaml::parseFile($this->translationsDir . '/tr_TR.yaml'));
        $jaKeys = $this->flattenKeys(Yaml::parseFile($this->translationsDir . '/ja_JP.yaml'));

        $this->assertNotEmpty($enKeys);
        $this->assertSame([], array_values(array_diff($enKeys, $trKeys)), 'Missing keys in tr_TR.yaml');
        $this->assertSame([], array_values(array_diff($trKeys, $enKeys)), 'Extra keys in tr_TR.yaml');
        $this->assertSame([], array_values(array_diff($enKeys, $jaKeys)), 'Missing keys in ja_JP.yaml');
        $this->assertSame([], array_values(array_diff($jaKeys, $enKeys)), 'Extra keys in ja_JP.yaml');
    }

    public function testKoreanPartialKeysExistInEnglish(): void
    {
        $enKeys = $this->flattenKeys(Yaml::parseFile($this->translationsDir . '/en_EN.yaml'));
        $krKeys = $this->flattenKeys(Yaml::parseFile($this->translationsDir . '/kr_KR.yaml'));

        $this->assertNotEmpty($krKeys);
        $this->assertSame([], array_values(array_diff($krKeys, $enKeys)), 'Unknown keys in kr_KR.yaml');
    }

    private function flattenKeys(array $data, string $prefix = ''): array
    {
        $keys = [];
        foreach ($data as $key => $value) {
            $fullKey = $prefix !== '' ? $prefix . '.' . $key : (string) $key;
            if (is_array($value)) {
                $keys = array_merge($keys, $this->flattenKeys($value, $fullKey));
            } else {
                $keys[] = $fullKey;
            }
        }
        return $keys;
    }
}
