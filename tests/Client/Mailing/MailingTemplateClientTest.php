<?php

namespace Scn\EvalancheSoapApiConnector\Client\Mailing;

use PHPUnit\Framework\MockObject\MockObject;
use Scn\EvalancheSoapApiConnector\EvalancheSoapClient;
use Scn\EvalancheSoapApiConnector\Extractor\ExtractorInterface;
use Scn\EvalancheSoapApiConnector\Hydrator\Config\HydratorConfigFactoryInterface;
use Scn\EvalancheSoapApiConnector\Hydrator\Config\HydratorConfigInterface;
use Scn\EvalancheSoapApiConnector\Mapper\ResponseMapperInterface;
use Scn\EvalancheSoapApiConnector\TestCase;
use Scn\EvalancheSoapStruct\Struct\Generic\ResourceInformationInterface;
use Scn\EvalancheSoapStruct\Struct\Mailing\MailingArticleInterface;

/**
 * Class MailingTemplateClientTest
 *
 * @package Scn\EvalancheSoapApiConnector\Client\Mailing
 */
class MailingTemplateClientTest extends TestCase
{
    /**
     * @var MailingTemplateClient
     */
    private $subject;

    /**
     * @var EvalancheSoapClient|MockObject
     */
    private $soapClient;

    /**
     * @var ResponseMapperInterface|MockObject
     */
    private $responseMapper;

    /**
     * @var HydratorConfigFactoryInterface|MockObject
     */
    private $hydratorConfigFactory;

    /**
     * @var ExtractorInterface|MockObject
     */
    private $extractor;

    public function setUp(): void
    {
        $this->soapClient = $this->getWsdlMock([
            'rename',
            'addArticles',
            'getArticles',
            'removeAllArticles',
            'removeArticles',
            'applyTemplate',
        ]);
        $this->responseMapper = $this->createMock(ResponseMapperInterface::class);
        $this->hydratorConfigFactory = $this->createMock(HydratorConfigFactoryInterface::class);
        $this->extractor = $this->createMock(ExtractorInterface::class);

        $this->subject = new MailingTemplateClient(
            $this->soapClient,
            $this->responseMapper,
            $this->hydratorConfigFactory,
            $this->extractor
        );
    }

    public function testUpdateTitleCanReturnInstanceOfResourceInformationInterface(): void
    {
        $id = 123;
        $title = 'some title';

        $config = $this->createMock(HydratorConfigInterface::class);
        $object = $this->createMock(ResourceInformationInterface::class);

        $response = new \stdClass();
        $response->renameResult = $object;

        $this->hydratorConfigFactory->expects($this->once())
            ->method('createResourceInformationConfig')
            ->willReturn($config);
        
        $this->soapClient->expects($this->once())
            ->method('rename')
            ->with([
                'resource_id' => $id,
                'name' => $title,
            ])
            ->willReturn($response);
        
        $this->responseMapper->expects($this->once())
            ->method('getObject')
            ->with(
                $response,
                'renameResult',
                $config
            )
            ->willReturn($response->renameResult);

        $this->assertInstanceOf(
            ResourceInformationInterface::class,
            $this->subject->updateTitle($id, $title)
        );
    }

    public function testRemoveArticlesCanReturnArrayOfMailingArticle(): void
    {
        $id = 1234;
        $referenceIds = [
            1,
            2,
            3,
            4,
        ];

        $config = $this->createMock(HydratorConfigInterface::class);
        $object = $this->createMock(MailingArticleInterface::class);
        $otherObject = $this->createMock(MailingArticleInterface::class);

        $response = new \stdClass();
        $response->removeArticlesResult = [
            $object,
            $otherObject
        ];

        $this->hydratorConfigFactory->expects($this->once())
            ->method('createMailingArticleConfig')
            ->willReturn($config);
        
        $this->soapClient->expects($this->once())
            ->method('removeArticles')
            ->with([
                'mailing_template_id' => $id,
                'reference_ids' => $referenceIds,
            ])
            ->willReturn($response);
        
        $this->responseMapper->expects($this->once())
            ->method('getObjects')->with(
                $response,
                'removeArticlesResult',
                $config
            )
            ->willReturn($response->removeArticlesResult);

        $this->assertContainsOnlyInstancesOf(
            MailingArticleInterface::class,
            $this->subject->removeArticles($id, $referenceIds)
        );
    }
    
    public function testAddArticlesAdd(): void
    {
        $id = 1234;
        $articles = [
            $this->createMock(MailingArticleInterface::class),
            $this->createMock(MailingArticleInterface::class)
        ];

        $config = $this->createMock(HydratorConfigInterface::class);

        $response = new \stdClass();
        $response->addArticlesResult = $articles;

        $extractedData = [
            [
                'some' => 'data'
            ],
            [
                'some' => 'other data'
            ]
        ];

        $this->extractor->expects($this->once())
            ->method('extractArray')
            ->with(
                $config,
                $articles
            )
            ->willReturn($extractedData);
        
        $this->hydratorConfigFactory->expects($this->exactly(2))
            ->method('createMailingArticleConfig')
            ->willReturn($config);
        
        $this->soapClient->expects($this->once())
            ->method('addArticles')
            ->with([
                'mailing_template_id' => $id,
                'articles' => $extractedData
            ])
            ->willReturn($response);
        
        $this->responseMapper->expects($this->once())
            ->method('getObjects')
            ->with(
                $response,
                'addArticlesResult',
                $config
            )
            ->willReturn($response->addArticlesResult);

        $this->assertContainsOnlyInstancesOf(
            MailingArticleInterface::class,
            $this->subject->addArticles($id, $articles)
        );
    }
    
    public function testGetArticlesByMailingTemplateIdReturnsListOfMailingArticle(): void
    {
        $id = 123;

        $config = $this->createMock(HydratorConfigInterface::class);
        $object = $this->createMock(MailingArticleInterface::class);
        $otherObject = $this->createMock(MailingArticleInterface::class);

        $response = new \stdClass();
        $response->getArticlesResult = [
            $object,
            $otherObject
        ];

        $this->hydratorConfigFactory->expects($this->once())
            ->method('createMailingArticleConfig')
            ->willReturn($config);
        
        $this->soapClient->expects($this->once())
            ->method('getArticles')
            ->with([
                'mailing_template_id' => $id,
            ])
            ->willReturn($response);
        
        $this->responseMapper->expects($this->once())
            ->method('getObjects')
            ->with(
                $response,
                'getArticlesResult',
                $config
            )
            ->willReturn($response->getArticlesResult);

        $this->assertContainsOnlyInstancesOf(
            MailingArticleInterface::class,
            $this->subject->getArticlesByMailingTemplateId($id)
        );
    }
    
    public function testRemoveAllArticlesRemoves(): void
    {
        $id = 123;

        $response = new \stdClass();
        $response->removeAllArticlesResult = true;

        $this->soapClient->expects($this->once())
            ->method('removeAllArticles')
            ->with(['mailing_template_id' => $id])
            ->willReturn($response);
        
        $this->responseMapper->expects($this->once())
            ->method('getBoolean')
            ->willReturn($response->removeAllArticlesResult);

        $this->assertTrue($this->subject->removeAllArticles($id));
    }
    
    public function testApplyTemplateApplies(): void
    {
        $id = 123;
        $mailingId = 666;

        $response = new \stdClass();
        $response->applyTemplateResult = true;

        $this->soapClient->expects($this->once())
            ->method('applyTemplate')
            ->with(['mailing_template_id' => $id, 'mailing_ids' => [$mailingId]])
            ->willReturn($response);

        $this->responseMapper->expects($this->once())
            ->method('getBoolean')
            ->willReturn($response->applyTemplateResult);

        $this->assertTrue($this->subject->applyTemplate($id, [$mailingId]));
    }
}
