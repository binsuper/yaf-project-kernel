<?php

namespace Gino\Yaf\Kernel\Cache\Marshaller;

use Symfony\Component\Cache\Marshaller\MarshallerInterface;

class JsonMarshaller implements MarshallerInterface {

    /**
     * @inheritDoc
     */
    public function marshall(array $values, ?array &$failed): array {
        $serialized = $failed = [];
        foreach ($values as $id => $value) {
            try {
                $json = json_encode($value, JSON_UNESCAPED_UNICODE);
                if ($json) {
                    $serialized[$id] = $json;
                } else {
                    $failed[] = $id;
                }
            } catch (\Exception $e) {
                $failed[] = $id;
            }
        }

        return $serialized;
    }

    /**
     * @inheritDoc
     */
    public function unmarshall(string $value) {
        $result = json_decode($value, true);
        return is_null($result) ? $value : $result;
    }

}