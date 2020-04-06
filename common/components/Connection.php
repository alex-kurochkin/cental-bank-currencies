<?php

namespace common\components;

/** @inheritdoc */
class Connection extends \yii\db\Connection
{

    /**
     * Get dsn attribute
     *
     * @param string $name
     *
     * @return null|string
     */
    protected function getDsnAttribute(string $name): ?string
    {
        if (preg_match('/' . $name . '=([^;]*)/', $this->dsn, $match)) {
            return $match[1];
        } else {
            return null;
        }
    }

    /**
     * Get data base name
     *
     * @return string
     */
    public function getDbName(): string
    {
        return $this->getDsnAttribute('dbname');
    }

    /**
     * Set data base name
     *
     * @param string $name
     *
     * @return Connection
     */
    public function setDbName(string $name): self
    {
        $this->dsn = str_replace($this->getDbName(), $name, $this->dsn);

        return $this;
    }

    /**
     * Reconnect to data base
     */
    public function reconnect(): void
    {
        $this->close();
        $this->open();
    }
}