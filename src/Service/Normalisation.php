<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Normalisation
{
    protected $translator;
    protected $normalizer;

    public function __construct(TranslatorInterface $translator, NormalizerInterface $normalizer)
    {
        $this->translator = $translator;
        $this->normalizer = $normalizer;
    }

    // Normalise l'entité
    public function normalize($entity, $nameEntity = null)
    {
        $array = $this->normalizer->normalize($entity, null, ['groups' => 'export']);

        foreach ($array as $key => $value) {
            if ($value && stristr($key, 'Date')) {
                $array[$key] = Date::stringToExcel(substr($value, 0, 10));
            }
            $newKey = $key.'#'.$nameEntity;
            $array[$newKey] = $array[$key];
            unset($array[$key]);
        }

        return $array;
    }

    // Inverse l'écriture en camelCase
    public function unCamelCase($content, $separator = ' ')
    {
        $content = preg_replace('#(?<=[a-zA-Z])([A-Z])(?=[a-zA-Z])#', $separator.'$1', $content);

        return $this->translator->trans(ucfirst(strtolower($content)), [], 'evaluation');
    }

    public function getKeys($array)
    {
        $arrayKeys = [];
        foreach ($array as $value) {
            if (stristr($value, '#')) {
                $array = explode('#', $value, 2);
                $nameEntity = $array[1];
                $value = $this->unCamelCase(str_replace('ToString', '', $array[0]), ' ');
                $nameEntity = $this->unCamelCase($nameEntity, ' ');
                $arrayKeys[] = $value.' ['.$nameEntity.']';
            } else {
                $arrayKeys[] = $value;
            }
        }

        return $arrayKeys;
    }
}
