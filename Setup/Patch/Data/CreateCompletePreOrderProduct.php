<?php
namespace Adithyan\TransferMedia\Setup\Patch\Data;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status as SourceStatus;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area as AppArea;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;

class CreateCompletePreOrderProduct implements DataPatchInterface, PatchRevertableInterface
{
    const COMPLETE_PRE_ORDER_PROD_SKU = 'complete-pre-order';

    public function __construct(
        protected ProductFactory $productFactory,
        protected ProductRepositoryInterface $productRepository,
        protected SearchCriteriaBuilder $searchCriteriaBuilder,
        protected AppState $appState,
        protected ModuleDirReader $moduleDirReader
    ) {
    }

    public function apply()
    {
        $sku = self::COMPLETE_PRE_ORDER_PROD_SKU;
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('sku', $sku)->create();
        $productList = $this->productRepository->getList($searchCriteria);
        
        if ($productList->getTotalCount() > 0) {
            // Product already exists, skip creating a new one
            return;
        }

        $this->appState->setAreaCode(AppArea::AREA_ADMINHTML);
        // Create the virtual product
        $product = $this->productFactory->create();
        $product->setSku($sku);
        $product->setName('Complete Pre Order');
        $product->setAttributeSetId(4);
        $product->setPrice(0);
        $product->setTypeId(ProductType::TYPE_VIRTUAL);
        $product->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);
        $product->setStatus(SourceStatus::STATUS_ENABLED);
        $product->setWeight(0);
        $product->setTaxClassId(2);
        $product->setStockData(
            [
                'manage_stock' => 1,
                'qty' => 999, 
                'is_in_stock' => 1
            ]
        );

        $this->productRepository->save($product);
        $viewDir = $this->moduleDirReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
            'QBurst_PreOrder'
        );
        // $imagePath = $viewDir . "/frontend/web/images/pre-order/complete-pre-order.jpg"; // path of the image
        // $imagePath = __DIR__ . '/../../src/complete-pre-order.jpg';
        $imagePath = "complete-pre-order.jpg";
        $product->addImageToMediaGallery($imagePath, array('image', 'small_image', 'thumbnail'), false, false);
        $product->save();
    }

    /**
     * Revert the patch
     */
    public function revert()
    {
        $sku = self::COMPLETE_PRE_ORDER_PROD_SKU;
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('sku', $sku)->create();
        $productList = $this->productRepository->getList($searchCriteria);

        if ($productList->getTotalCount() > 0) {
            $product = $productList->getItems()[0];
            $this->productRepository->delete($product);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
