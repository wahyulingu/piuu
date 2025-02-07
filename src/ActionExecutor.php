<?php

namespace WahyuLingu\Piuu;

class ActionExecutor
{
    /**
     * Menjalankan sebuah aksi (closure) dengan optional callback.
     *
     * Callback memiliki signature:
     *   function(string $phase, string $actionName, $data): void
     *
     * @param  callable  $action  Aksi yang akan dijalankan.
     * @param  string  $actionName  Nama aksi (misalnya "sendKey", "click", "delay").
     * @param  mixed  $data  Data tambahan untuk callback.
     * @param  callable|null  $callback  Optional callback.
     * @return mixed Hasil dari aksi.
     */
    public function execute(callable $action, string $actionName, $data = null, ?callable $callback = null)
    {
        if ($callback) {
            $callback('before', $actionName, $data);
        }
        $result = $action();
        if ($callback) {
            $callback('after', $actionName, $data);
        }

        return $result;
    }
}
