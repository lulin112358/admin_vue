<?php


namespace TypesettingApiPackage\Model;


class ReUploadModel extends BaseModel
{

    /**
     * 标题
     * @var string
     */
    private $title;

    /**
     * 作者
     * @var string
     */
    private $author;

    /**
     * 文件
     * @var object
     */
    private $file;

    /**
     * 排版记录ID
     * @var string
     */
    private $rid;

    /**
     * 模板id
     * @var string
     */
    private $schema_id;

    /**
     * @param string $rid
     */
    public function setRid($rid)
    {
        $this->rid = $rid;
        $this->signParams['rid'] = $rid;
    }

    /**
     * @return string
     */
    public function getRid()
    {
        return $this->rid;
    }
    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        $this->signParams['title'] = $title;
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
        $this->signParams['author'] = $author;
    }

    /**
     * @param string $schema_id
     */
    public function setSchemaId($schema_id)
    {
        $this->schema_id = $schema_id;
        $this->signParams['schemaId'] = $schema_id;
    }

    /**
     * @param object $file
     */
    public function setFile($file)
    {
        $this->file = $file;
        $this->notSignParams['file'] = $file;
    }


}