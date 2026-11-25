<?php

/**
 * Copyright © 2025-present bariscodefx
 * 
 * This file is part of project Hiro Discord Bot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace hiro\parts;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;

/**
 * Language
 */
class Language
{
    /**
     * Translator
     *
     * @var Translator
     */
    public Translator $translator;

    public function __construct(string $locale = 'en_EN')
    {
        $translator = new Translator($locale);
        $translator->addLoader('yaml', new YamlFileLoader());

        $translator->addResource('yaml', dirname(__DIR__, 2) . "/translations/en_EN.yaml", 'en_EN');
        $translator->addResource('yaml', dirname(__DIR__, 2) . "/translations/tr_TR.yaml", 'tr_TR');
        $translator->addResource('yaml', dirname(__DIR__, 2) . "/translations/kr_KR.yaml", 'kr_KR');
        $translator->addResource('yaml', dirname(__DIR__, 2) . "/translations/ja_JP.yaml", 'ja_JP');

        $translator->setFallbackLocales(['en_EN']);

        $this->translator = $translator;
    }

    /**
     * Returns translator object
     *
     * @return Translator
     */
    public function getTranslator(): Translator
    {
        return $this->translator;
    }

}
