<?php


namespace TendoPay\SDK\Models;


class Item
{
    /**
     * Titile of the item
     * @var string $title
     */
    private $title;

    /**
     * Price of the item
     * @var float $price
     */
    private $price;

    /**
     * TendoPayItem constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->title = $params['item_title'] ?? '';
        $this->price = $params['item_price'] ?? 0;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Item
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return Item
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'price' => $this->price,
        ];
    }


}
