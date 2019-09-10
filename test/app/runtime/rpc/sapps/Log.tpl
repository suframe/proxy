interface Log
{

    /**
     * 根据key获取配置
     * @param $key
     * @param array $params
     * @return array
     */
    public function search ($key, $params);
    
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