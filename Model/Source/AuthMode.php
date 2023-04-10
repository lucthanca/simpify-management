<?php
declare(strict_types=1);
namespace SimiCart\SimpifyManagement\Model\Source;

class AuthMode implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Offline auth mode.
     *
     * @var int
     */
    const OFFLINE = 0;

    /**
     * Per-user auth mode.
     *
     * @var int
     */
    const PERUSER = 1;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $reflect = new \ReflectionClass(get_called_class());
        $constants = $reflect->getConstants();
        $options = [];
        foreach ($constants as $label => $value) {
            $options[] = [
                'value' => $value, 'label' => __($label)
            ];
        }
        return $options;
    }

    /**
     * Get constants key name by constant value
     *
     * @param int $value
     * @return string
     */
    public static function toNative(int $value): string
    {
        if ($value === static::PERUSER) {
            return 'PER-USER';
        }
        return 'OFFLINE';
    }
}
