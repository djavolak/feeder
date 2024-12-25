import Product from "./Product.js";
import Category from "./Category.js";
import CategoryMapper from "./CategoryMapper.js";
import Supplier from "./Supplier.js";
import Attribute from "./Attribute.js";
import Image from "./Image.js";
import Activity from "./Activity.js";
import Tenant from "./Tenant.js";
import AttributeGroup from "./AttributeGroup.js";
import MarginGroups from "./MarginGroups.js";
import Tag from "./Tag.js";
import User from "./User.js";
import AttributeValues from "./AttributeValues.js";
import CategoryParsingRules from "./CategoryParsingRules.js";
import AttributeMapping from "./AttributeMapping.js";
import Post from "./Post.js";
import SupplierFieldMapping from "./SupplierFieldMapping.js";
import SourceProduct from "./SourceProduct.js";
import ParsedProduct from "./ParsedProduct.js";

export default class PageFactory {

    static make(page) {
        switch (page) {
            case 'Activity':
                return new Activity();
            case 'Category':
                return new Category();
            case 'Image':
                return new Image();
            case 'Tenant':
                return new Tenant();
            case 'User':
                return new User();
            case 'CategoryMapper':
                return new CategoryMapper();
            case 'Product':
                return new Product();
            case 'Supplier':
                return new Supplier();
            case 'Attribute':
                return new Attribute();
            case 'AttributeGroup':
                return new AttributeGroup();
            case 'Margin-groups':
                return new MarginGroups();
            case 'Tag':
                return new Tag();
            case 'AttributeValues':
                return new AttributeValues();
            case 'Category-parsing-rules':
                return new CategoryParsingRules();
            case 'Attribute-mapping':
                return new AttributeMapping();
            case 'Post':
                return new Post();
            case 'SourceProduct':
                return new SourceProduct();
            case 'ParsedProduct':
                return new ParsedProduct();
            case 'SupplierFieldMapping':
                return new SupplierFieldMapping();
        }
    }
}