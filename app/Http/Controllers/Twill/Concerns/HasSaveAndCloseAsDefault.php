<?php
namespace App\Http\Controllers\Twill\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasSaveAndCloseAsDefault
{
    public function getSubmitOptions(Model $item): array|null
    {
        // Find "save" options from ModuleController or original Trait
        $options = parent::getSubmitOptions($item);

        if (is_array($options)) {
            foreach ($options as $key => $values) {
                $closeIndex = null;
                // Find "Save and Close" option
                foreach ($values as $index => $option) {
                    if (isset($option['name']) && str_ends_with($option['name'], '-close')) {
                        $closeIndex = $index;
                        break;
                    }
                }

                // Move it as first option
                if ($closeIndex !== null) {
                    $closeOption = $values[$closeIndex];
                    unset($values[$closeIndex]);
                    array_unshift($values, $closeOption);
                    $options[$key] = array_values($values);
                }
            }
        }

        return $options;
    }
}