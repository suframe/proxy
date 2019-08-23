interface Server
{

    /**
     * 写入日志
     * @param string $type
     * @param array $data
     * @param null $mark
     * @return string
     * @throws \Exception
     */
    public function write (string $type, $data, $mark);
    
}