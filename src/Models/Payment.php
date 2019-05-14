<?php


namespace TendoPay\SDK\Models;


class Payment
{
    /**
     * Id (required)
     * @var string $merchantOrderId
     */
    private $merchantOrderId;

    /**
     * Title (required)
     * @ver string $title
     */
    private $description;

    /**
     * Total amount of the order (required)
     * @var float $requestAmount
     */
    private $requestAmount;

    /**
     * Details in the order (optional)
     * @var Item[] $items
     */
    private $items;

    /**
     * TendoPayOrder constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->merchantOrderId = $params['merchant_order_id'] ?? null;
        $this->description = $params['description'] ?? '';
        $this->requestAmount = $params['request_amount'] ?? 0;
    }

    /**
     * @return null|string
     */
    public function getMerchantOrderId(): ?string
    {
        return $this->merchantOrderId;
    }

    /**
     * @param string $merchantOrderId
     * @return Payment
     */
    public function setMerchantOrderId(string $merchantOrderId): self
    {
        $this->merchantOrderId = $merchantOrderId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Payment
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return float
     */
    public function getRequestAmount(): float
    {
        return $this->requestAmount ?? 0;
    }

    /**
     * @param float $requestAmount
     * @return Payment
     */
    public function setRequestAmount(float $requestAmount): self
    {
        $this->requestAmount = $requestAmount;
        return $this;
    }

    /**
     * Return TendoPayItem[]
     * @return array
     */
    public function getItems(): array
    {
        return $this->items ?? [];
    }

    /**
     * @param array $items
     * @return Payment
     */
    public function setItems(array $items): self
    {
        $this->items = $items;
        return $this;
    }
}
